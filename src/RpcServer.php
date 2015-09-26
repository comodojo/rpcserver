<?php namespace Comodojo\RpcServer;

use \Comodojo\RpcServer\Component\Capabilities;
use \Comodojo\RpcServer\Component\Methods;
use \Comodojo\RpcServer\Component\Errors;
use \Comodojo\RpcServer\Request\Parameters;
use \Comodojo\RpcServer\Request\XmlProcessor;
use \Comodojo\RpcServer\Request\JsonProcessor;
use \Comodojo\Xmlrpc\XmlrpcEncoder;
use \Comodojo\Xmlrpc\XmlrpcDecoder;
use \Crypt_AES;
use \Comodojo\Exception\RpcException;
use \Comodojo\Exception\XmlrpcException;
use \Exception;


/** 
 * tbw
 *
 * It optionally supports a not standard encrypted transport
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

    const XMLRPC = 'xml';
    
    const JSONRPC = 'json';

    private $capabilities = null;
    
    private $methods = null;
    
    private $errors = null;
    
    private $payload = null;
    
    private $encrypt = null;
    
    private $encoding = null;
    
    private $request_is_encrypted = false;
    
    private $protocol = null;
    
    private $supported_protocols = array('xml','json');
    
    public function __construct($protocol) {
        
        try {
            
            $this->setProtocol($protocol);
            
        } catch (Exception $e) {
            
            throw $e;
            
        }
        
        $this->capabilities = new Capabilities();
        
        $this->methods = new Methods();
        
        $this->errors = new Errors();
        
        self::setIntrospectionMethods($this->methods);
        
        self::setCapabilities($this->capabilities);
        
    }
    
    final public function setProtocol($protocol) {
        
        if ( empty($protocol) || !in_array($protocol, $this->supported_protocols) ) throw new Exception('Invalid or unsupported RPC protocol');
        
        $this->protocol = $protocol;
        
        return $this;
        
    }
    
    final public function getProtocol() {
        
        return $this->protocol;
        
    }
    
    final public function setPayload($payload) {
        
        $this->payload = $payload;
        
        return $this;
        
    }
    
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
     * @return  \Comodojo\RpcClient\RpcServer
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
    
    public function capabilites() {

        return $this->capabilities;

    }
    
    public function methods() {

        return $this->methods;

    }
    
    public function errors() {

        return $this->errors;
    }
    
    public function serve() {
        
        $response = null;
        
        $parameters_object = new Parameters($this->capabilities, $this->methods, $this->errors);
        
        try {
            
            $payload = $this->uncan($this->payload);
            
            if ( $this->protocol == self::XMLRPC ) $result = XmlProcessor::process($payload, $parameters_object);
            
            else if ( $this->protocol == self::JSONRPC ) $result = JsonProcessor::process($payload, $parameters_object);
            
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
        
        if ( empty($payload) ) throw new RpcException("Invalid Request", -32600);
        
        if ( substr($payload,0,27) == 'comodojo_encrypted_request-' ) {
            
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
                
            } catch ( XmlrpcException $xe ) {
                
                throw new RpcException("Parse error", -32700);
                
            }
            
        } else if ( $this->protocol == 'json' ) {
            
            $decoded = json_decode($payload, false /*DO RAW conversion*/);
            
            if ( is_null($decoded) ) throw new RpcException("Parse error", -32700);
            
        } else {
            
            throw new RpcException("Transport error", -32300);
            
        }
        
        return $decoded;
        
    }
    
    private function can($response, $error) {
        
        $encoded = null;
        
        if ( $this->protocol == 'xml' ) {
            
            $encoder = new XmlrpcEncoder();
            
            try {

                $encoded = $error ? $encoder->encodeError($response->getCode(), $response->getMessage()) : $encoder->encodeResponse($response);
                
            } catch ( XmlrpcException $xe ) {
                
                $encoded = $encoder->encodeError(-32500, "Application error");

            }
            
        } else {
            
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
            ->addParameter('string','method');
            
        $methods->add($method_help);
        
        $method_signature = RpcMethod::create("system.methodSignature", "\Comodojo\RpcServer\Introspection\MethodSignature", "execute")
            ->setDescription("Returns an array of known signatures (an array of arrays) for the method name passed. 
        If no signatures are known, returns a none-array (test for type != array to detect missing signature)")
            ->setReturnType('array')
            ->addParameter('string','method');
        
        $methods->add($method_signature);
            
        $multicall = RpcMethod::create("system.multicall", "\Comodojo\RpcServer\Reserved\Multicall", "execute")
            ->setDescription("Boxcar multiple RPC calls in one request. See http://www.xmlrpc.com/discuss/msgReader\$1208 for details")
            ->setReturnType('array')
            ->addParameter('array','requests');
            
        $methods->add($multicall);
    
    }
    
    private static function setCapabilities($capabilities) {
    
        $capabilities->add('xmlrpc', 'http://www.xmlrpc.com/spec', 1);
        
        $capabilities->add('system.multicall', 'http://www.xmlrpc.com/discuss/msgReader$1208', 1);
        
        $capabilities->add('introspection', 'http://phpxmlrpc.sourceforge.net/doc-2/ch10.html', 2);
        
        $capabilities->add('nil', 'http://www.ontosys.com/xml-rpc/extensions.php', 1);
        
        $capabilities->add('faults_interop', 'http://xmlrpc-epi.sourceforge.net/specs/rfc.fault_codes.php', 20010516);
        
        $capabilities->add('json-rpc', 'http://www.jsonrpc.org/specification', 2);
    
    }
    
 }
 