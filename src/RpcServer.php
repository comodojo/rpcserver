<?php namespace Comodojo\RpcServer;

use \Comodojo\RpcServer\Component\Capabilities;
use \Comodojo\RpcServer\Component\Methods;
use \Comodojo\RpcServer\Component\Errors;
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

    private $capabilities = null;
    
    private $methods = null;
    
    private $errors = null;
    
    public function __construct($protocol) {
        
        $this->capabilities = new Capabilities();
        
        $this->methods = new Methods();
        
        $this->errors = new Errors();
        
        self::setIntrospectionMethods($this->methods);
        
        self::setCapabilities($this->capabilities);
        
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
    
    public function serve() {}
    
    static private function setIntrospectionMethods($methods) {
        
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
            ->addParameter('string');
            
        $methods->add($method_help);
        
        $method_signature = RpcMethod::create("system.methodSignature", "\Comodojo\RpcServer\Introspection\MethodSignature", "execute")
            ->setDescription("Returns an array of known signatures (an array of arrays) for the method name passed. 
        If no signatures are known, returns a none-array (test for type != array to detect missing signature)")
            ->setReturnType('array')
            ->addParameter('string');
        
        $methods->add($method_signature);
            
        $multicall = RpcMethod::create("system.multicall", "\Comodojo\RpcServer\Reserved\Multicall", "execute")
            ->setDescription("Boxcar multiple RPC calls in one request. See http://www.xmlrpc.com/discuss/msgReader\$1208 for details")
            ->setReturnType('array')
            ->addParameter('array');
            
        $methods->add($multicall);
    
    }
    
    static private function setCapabilities($capabilities) {
    
        $capabilities->add('xmlrpc', 'http://www.xmlrpc.com/spec', 1);
        
        $capabilities->add('system.multicall', 'http://www.xmlrpc.com/discuss/msgReader$1208', 1);
        
        $capabilities->add('introspection', 'http://phpxmlrpc.sourceforge.net/doc-2/ch10.html', 2);
        
        $capabilities->add('nil', 'http://www.ontosys.com/xml-rpc/extensions.php', 1);
        
        $capabilities->add('faults_interop', 'http://xmlrpc-epi.sourceforge.net/specs/rfc.fault_codes.php', 20010516);
        
        $capabilities->add('json-rpc', 'http://www.jsonrpc.org/specification', 2);
    
    }
    
    
 }
 