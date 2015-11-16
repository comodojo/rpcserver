<?php namespace Comodojo\RpcServer\Request;

use \Comodojo\RpcServer\Component\Capabilities;
use \Comodojo\RpcServer\Component\Methods;
use \Comodojo\RpcServer\Component\Errors;
use \Psr\Log\LoggerInterface;

/** 
 * The parameters object
 * 
 * It provides to each RPC method a way to access:
 *  - Provided parameters
 *  - Supported capabilities
 *  - Implemented methods
 *  - Predefined errors
 *  - Current RPC protocol
 *  - Current logger
 * 
 * The parameter object is the only one parameter passed to a RPC method's implementation
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
 
class Parameters {

    /**
     * Array of provided parameters
     *
     * @var array
     */
    private $parameters = array();
    
    /**
     * Supported capabilities
     *
     * @var \Comodojo\Component\Capabilities
     */
    private $capabilities = null;
    
    /**
     * Implemented methods
     *
     * @var \Comodojo\Component\Methods
     */
    private $methods = null;
    
    /**
     * Predefined errors
     *
     * @var \Comodojo\Component\Errors
     */
    private $errors = null;

    /**
     * Current RPC protocol (json|rpc)
     *
     * @var string
     */
    private $protocol = null;

    /**
     * Current logger
     *
     * @var \Psr\Log\LoggerInterface
     */
    private $logger = null;
    
    /**
     * Class constructor
     *
     * @param \Comodojo\Component\Capabilities  $capabilities
     * @param \Comodojo\Component\Methods       $methods
     * @param \Comodojo\Component\Errors        $errors
     * @param \Psr\Log\LoggerInterface          $logger
     * @param string                            $protocol
     */
    public function __construct(Capabilities $capabilities, Methods $methods, Errors $errors, LoggerInterface $logger, $protocol) {
        
        $this->capabilities = $capabilities;
        
        $this->methods = $methods;
        
        $this->errors = $errors;

        $this->logger = $logger;

        $this->protocol = $protocol;
        
    }
    
    /**
     * Set provided parameters
     *
     * @param array $parameters
     * 
     * @return Comodojo\RpcServer\Request\Parameters
     */
    public function setParameters($parameters) {
        
        $this->parameters = $parameters;
        
        return $this;
        
    }
    
    /**
     * Get capabilities object
     *
     * @return \Comodojo\Component\Capabilities
     */
    final public function capabilities() {
        
        return $this->capabilities;
        
    }
    
    /**
     * Get methods object
     *
     * @return \Comodojo\Component\Methods
     */
    final public function methods() {
        
        return $this->methods;
        
    }
    
    /**
     * Get errors object
     *
     * @return \Comodojo\Component\Errors
     */
    final public function errors() {
        
        return $this->errors;
        
    }

    /**
     * Get current RPC protocol
     *
     * @return string
     */
    final public function protocol() {

        return $this->protocol;

    }

    /**
     * Get current logger instance
     *
     * @return \Psr\Log\LoggerInterface
     */
    final public function logger() {

        return $this->logger;

    }
    
    /**
     * Get parameter(s)
     * 
     * @param string $parameter (optional) The parameter name (null will return whole array of parameters)
     *
     * @return mixed
     */
    public function get($parameter = null) {
        
        if ( empty($parameter) ) {
            
            $return = $this->parameters;
            
        } else if ( array_key_exists($parameter, $this->parameters) ) {
            
            $return = $this->parameters[$parameter];
            
        } else {
            
            $return = null;
        
        }
        
        return $return;
        
    }
    
}
