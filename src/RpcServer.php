<?php namespace Comodojo\RpcServer;

use \Comodojo\RpcServer\Component\Capabilities;
use \Comodojo\RpcServer\Component\Methods;
use \Comodojo\RpcServer\Component\Errors;
use \Comodojo\RpcServer\Request\Parameters;
use \Comodojo\RpcServer\Request\XmlProcessor;
use \Comodojo\RpcServer\Request\JsonProcessor;
use \Comodojo\RpcServer\Util\NullLogger;
use \Comodojo\Xmlrpc\XmlrpcEncoder;
use \Comodojo\Xmlrpc\XmlrpcDecoder;
use \Crypt_AES;
use \Comodojo\Exception\RpcException;
use \Comodojo\Exception\XmlrpcException;
use \Exception;


/** 
 * The RpcServer main class.
 * 
 * @package     Comodojo Spare Parts
 * @author      Marco Giovinazzi <marco.giovinazzi@comodojo.org>
 * @license     MIT
 *
 * LICENSE:
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
 
class RpcServer {

    /**
     * Capabilities collector
     *
     * @const string
     */
    const XMLRPC = 'xml';
    
    /**
     * Capabilities collector
     *
     * @const string
     */
    const JSONRPC = 'json';

    /**
     * Capabilities collector
     *
     * @var \Comodojo\RpcServer\Component\Capabilities
     */
    private $capabilities = null;
    
    /**
     * RpcMethods collector
     *
     * @var \Comodojo\RpcServer\Component\Methods
     */
    private $methods = null;
    
    /**
     * Standard Rpc Errors collector
     *
     * @var \Comodojo\RpcServer\Component\Errors
     */
    private $errors = null;
    
    /**
     * The request payload, better the RAW export of 'php://input'
     *
     * @var string
     */
    private $payload = null;
    
    /**
     * Encryption key, in case of encrypted transport
     *
     * @var string
     */
    private $encrypt = null;
    
    /**
     * Current encoding
     *
     * @var string
     */
    private $encoding = 'utf-8';
    
    /**
     * Current protocol
     *
     * @var string
     */
    private $protocol = null;
    
    /**
     * Supported RPC protocols
     *
     * @var array
     */
    private $supported_protocols = array('xml', 'json');

    /**
     * Internal marker (encryption)
     *
     * @var bool
     */
    private $request_is_encrypted = false;

    /**
     * Current logger
     *
     * @var \Psr\Log\LoggerInterface
     */
    private $logger = null;
    
    /**
     * Class constructor
     *
     * @param string $protocol
     *
     * @throws \Exception
     */
    public function __construct($protocol, \Psr\Log\LoggerInterface $logger = null) {
        
        $this->logger = is_null($logger) ? new NullLogger() : $logger;

        try {
            
            // setup protocol

            $this->setProtocol($protocol);

            // init components

            $this->capabilities = new Capabilities($this->logger);
        
            $this->methods = new Methods($this->logger);
            
            $this->errors = new Errors($this->logger);

            // populate components

            self::setIntrospectionMethods($this->methods);
            
            self::setCapabilities($this->capabilities);

            self::setErrors($this->errors);
            
        } catch (Exception $e) {
            
            throw $e;
            
        }
        
        $this->logger->notice("RpcServer init complete, protocol ".$protocol);
        
    }
    
    /**
     * Set RPC protocol (json or xml)
     *
     * @param string $protocol
     *
     * @return \Comodojo\RpcServer\RpcServer
     * @throws \Exception
     */
    final public function setProtocol($protocol) {
        
        if ( empty($protocol) || !in_array($protocol, $this->supported_protocols) ) throw new Exception('Invalid or unsupported RPC protocol');
        
        $this->protocol = $protocol;
        
        return $this;
        
    }
    
    /**
     * Get RPC protocol
     *
     * @return string
     */
    final public function getProtocol() {
        
        return $this->protocol;
        
    }
    
    /**
     * Set request payload, raw format
     *
     * @return \Comodojo\RpcServer\RpcServer
     */
    final public function setPayload($payload) {
        
        $this->payload = $payload;
        
        return $this;
        
    }
    
    /**
     * Get request payload
     *
     * @return string
     */
    final public function getPayload() {
        
        return $this->payload;
        
    }
    
    final public function setEncoding($encoding) {
        
        $this->encoding = $encoding;
        
        return $this;
        
    }
    
    final public function getEncoding() {
        
        return $this->encoding;
        
    }
    
    /**
     * Set encryption key; this will enable the NOT-STANDARD payload encryption
     *
     * @param   string  $key Encryption key
     *
     * @return  RpcServer
     * 
     * @throws \Exception
     */
    final public function setEncryption($key) {

        if ( empty($key) ) throw new Exception("Shared key cannot be empty");

        $this->encrypt = $key;

        return $this;

    }
    
    final public function getEncryption() {
        
        return $this->encrypt;
        
    }
    
    /**
     * Get the capabilities manager
     *
     * @return \Comodojo\RpcServer\Component\Capabilities
     */
    public function capabilities() {

        return $this->capabilities;

    }
    
    /**
     * Get the methods manager
     *
     * @return \Comodojo\RpcServer\Component\Methods
     */
    public function methods() {

        return $this->methods;

    }
    
    /**
     * Get the errors manager
     *
     * @return \Comodojo\RpcServer\Component\Errors
     */
    public function errors() {

        return $this->errors;
    }

    /**
     * Retrieve the logger instance
     *
     * @return \Psr\Log\LoggerInterface
     */
    public function logger() {

        return $this->logger;

    }
    
    /**
     * Serve request
     *
     * @return string
     * @throws Exception
     */
    public function serve() {

        $this->logger->notice("Start serving request");
        
        $parameters_object = new Parameters($this->capabilities, $this->methods, $this->errors, $this->logger, $this->protocol);
        
        try {

            $this->logger->debug("Received payload: ".$this->payload);
            
            $payload = $this->uncan($this->payload);

            $this->logger->debug("Decoded payload", (array)$payload);

            if ( $this->protocol == self::XMLRPC ) $result = XmlProcessor::process($payload, $parameters_object, $this->logger);
            
            else if ( $this->protocol == self::JSONRPC ) $result = JsonProcessor::process($payload, $parameters_object, $this->logger);
            
            else throw new Exception('Invalid or unsupported RPC protocol');
            
        } catch (RpcException $re) {
            
            return $this->can($re, true);
            
        } catch (Exception $e) {
            
            throw $e;
            
        }
        
        return $this->can($result, false);
        
    }
    
    private function uncan($payload) {
        
        $decoded = null;
        
        if ( empty($payload) || !is_string($payload) ) throw new RpcException("Invalid Request", -32600);
        
        if ( substr($payload, 0, 27) == 'comodojo_encrypted_request-' ) {
            
            if ( empty($this->encrypt) ) throw new RpcException("Transport error", -32300);
            
            $this->request_is_encrypted = true;
            
            $aes = new Crypt_AES();
            
            $aes->setKey($this->encrypt);
            
            $payload = $aes->decrypt(base64_decode(substr($payload, 27)));
            
            if ( $payload == false ) throw new RpcException("Transport error", -32300);
            
        }
        
        if ( $this->protocol == 'xml' ) {
            
            $decoder = new XmlrpcDecoder();
            
            try {

                $decoded = $decoder->decodeCall($payload);
                
            } catch (XmlrpcException $xe) {
                
                throw new RpcException("Parse error", -32700);
                
            }
            
        } else if ( $this->protocol == 'json' ) {

            if ( strtolower($this->encoding) != 'utf-8' ) {

                $payload = mb_convert_encoding($payload, "UTF-8", strtoupper($this->encoding));

            }
            
            $decoded = json_decode($payload, false /*DO RAW conversion*/);
            
            if ( is_null($decoded) ) throw new RpcException("Parse error", -32700);
            
        } else {
            
            throw new RpcException("Transport error", -32300);
            
        }
        
        return $decoded;
        
    }
    
    /**
     * @param boolean $error
     */
    private function can($response, $error) {
        
        $encoded = null;
        
        if ( $this->protocol == 'xml' ) {
            
            $encoder = new XmlrpcEncoder();

            $encoder->setEncoding($this->encoding);
            
            try {

                $encoded = $error ? $encoder->encodeError($response->getCode(), $response->getMessage()) : $encoder->encodeResponse($response);
                
            } catch (XmlrpcException $xe) {
                
                $encoded = $encoder->encodeError(-32500, "Application error");

            }
            
        } else {

            if ( strtolower($this->encoding) != 'utf-8' && !is_null($response) ) {

                array_walk_recursive($response, function(&$entry) {
                    
                    if ( is_string($entry) ) {

                        $entry = mb_convert_encoding($entry, strtoupper($this->encoding), "UTF-8");

                    }

                });
                
            }
            
            // json will not return any RpcException; errors (in case) are handled directly by processor
            
            $encoded = is_null($response) ? null : json_encode($response/*, JSON_NUMERIC_CHECK*/);
            
        }
        
        if ( $this->request_is_encrypted /* && !empty($encoded) */ ) {
            
            $aes = new Crypt_AES();
            
            $aes->setKey($this->encrypt);
            
            $encoded = 'comodojo_encrypted_response-'.base64_encode($aes->encrypt($encoded));
            
        }
        
        return $encoded;
        
    }
    
    /**
     * @param Methods $methods
     */
    private static function setIntrospectionMethods($methods) {
        
        $get_capabilities = RpcMethod::create("system.getCapabilities", "\Comodojo\RpcServer\Reserved\GetCapabilities", "execute")
            ->setDescription("This method lists all the capabilites that the RPC server has: the (more or less standard) extensions to the RPC spec that it adheres to")
            ->setReturnType('struct');
        
        $methods->add($get_capabilities);
        
        $list_methods = RpcMethod::create("system.listMethods", "\Comodojo\RpcServer\Introspection\ListMethods", "execute")
            ->setDescription("This method lists all the methods that the RPC server knows how to dispatch")
            ->setReturnType('array');
            
        $methods->add($list_methods);
        
        $method_help = RpcMethod::create("system.methodHelp", "\Comodojo\RpcServer\Introspection\MethodHelp", "execute")
            ->setDescription("Returns help text if defined for the method passed, otherwise returns an empty string")
            ->setReturnType('string')
            ->addParameter('string', 'method');
            
        $methods->add($method_help);
        
        $method_signature = RpcMethod::create("system.methodSignature", "\Comodojo\RpcServer\Introspection\MethodSignature", "execute")
            ->setDescription("Returns an array of known signatures (an array of arrays) for the method name passed. 
        If no signatures are known, returns a none-array (test for type != array to detect missing signature)")
            ->setReturnType('array')
            ->addParameter('string', 'method');
        
        $methods->add($method_signature);
            
        $multicall = RpcMethod::create("system.multicall", "\Comodojo\RpcServer\Reserved\Multicall", "execute")
            ->setDescription("Boxcar multiple RPC calls in one request. See http://www.xmlrpc.com/discuss/msgReader\$1208 for details")
            ->setReturnType('array')
            ->addParameter('array', 'requests');
            
        $methods->add($multicall);
    
    }
    
    /**
     * @param Capabilities $capabilities
     */
    private static function setCapabilities($capabilities) {

        $supported_capabilities = array(
            'xmlrpc' => array('http://www.xmlrpc.com/spec', 1),
            'system.multicall' => array('http://www.xmlrpc.com/discuss/msgReader$1208', 1),
            'introspection' => array('http://phpxmlrpc.sourceforge.net/doc-2/ch10.html', 2),
            'nil' => array('http://www.ontosys.com/xml-rpc/extensions.php', 1),
            'faults_interop' => array('http://xmlrpc-epi.sourceforge.net/specs/rfc.fault_codes.php', 20010516),
            'json-rpc' => array('http://www.jsonrpc.org/specification', 2)
        );
    
        foreach ($supported_capabilities as $capability => $values) {
            
            $capabilities->add($capability, $values[0], $values[1]);

        }
    
    }

    private static function setErrors($errors) {

        $std_rpc_errors = array(
            -32700 => "Parse error",
            -32701 => "Parse error - Unsupported encoding",
            -32702 => "Parse error - Invalid character for encoding",
            -32600 => "Invalid Request",
            -32601 => "Method not found",
            -32602 => "Invalid params",
            -32603 => "Internal error",
            -32500 => "Application error",
            -32400 => "System error",
            -32300 => "Transport error",
            // Predefined Comodojo Errors
            -31000 => "Multicall is available only in XMLRPC",
            -31001 => "Recursive system.multicall forbidden"
        );

        foreach ($std_rpc_errors as $code => $message) {
            
            $errors->add($code, $message);

        }
        
    }
    
 }
 