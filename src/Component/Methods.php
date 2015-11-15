<?php namespace Comodojo\RpcServer\Component;

/** 
 * RPC rpc_methods manager
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
 
class Methods {

    /**
     * Array of methods
     *
     * @var array
     */
    private $rpc_methods = array();

    /**
     * Current logger
     *
     * @var \Psr\Log\LoggerInterface
     */
    private $logger = null;

    /**
     * Class constructor
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(\Psr\Log\LoggerInterface $logger) {

        $this->logger = $logger;

    }
    
    /**
     * Add an RPC method
     *
     * @param \Comodojo\RpcServer\RpcMethod $method
     *
     * @return bool
     */
    final public function add(\Comodojo\RpcServer\RpcMethod $method) {
        
        $name = $method->getName();

        if ( array_key_exists($name, $this->rpc_methods) ) {

            $this->logger->warning("Cannot add method ".$name.": duplicate entry");
            
            return false;
            
        } else {
            
            $this->rpc_methods[$name] = $method;

            $this->logger->info("Added method ".$name);
            
            return true;
            
        }
        
    }
    
    /**
     * Delete a method
     *
     * @param string $name
     *
     * @return bool
     */
    final public function delete($name) {
        
        if ( array_key_exists($name, $this->rpc_methods) ) {
            
            unset($this->rpc_methods[$name]);

            $this->logger->info("Deleted method ".$name);
            
            return true;
            
        } else {
            
            $this->logger->warning("Cannot delete method ".$name.": entry not found");
            
            return false;
            
        }
        
    }
    
    /**
     * Get registered method(s)
     *
     * @param string $method
     *
     * @return array|\Comodojo\RpcServer\RpcMethod|null
     */
    final public function get($method = null) {
        
        if ( empty($method) ) {
            
            $return = $this->rpc_methods;
            
        } else if ( array_key_exists($method, $this->rpc_methods) ) {
            
            $return = $this->rpc_methods[$method];
        
        } else {
            
            $return = null;
            
        }
        
        return $return;
        
    }
    
}
