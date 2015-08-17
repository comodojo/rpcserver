<?php namespace Comodojo\RpcServer\Request;

use \Comodojo\RpcServer\Component\Capabilities;
use \Comodojo\RpcServer\Component\Methods;
use \Comodojo\RpcServer\Component\Errors;

/** 
 * tbw
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

    private $parameters = array();
    
    private $capabilities = array();
    
    private $methods = array();
    
    private $errors = array();
    
    public function __construct(Capabilities $capabilities, Methods $methods, Errors $errors) {
        
        $this->capabilities = $capabilities;
        
        $this->methods = $methods;
        
        $this->errors = $errors;
        
    }
    
    public function setParameters($parameters) {
        
        $this->parameters = $parameters;
        
        return $this;
        
    }
    
    final public function capabilities() {
        
        return $this->capabilities;
        
    }
    
    final public function methods() {
        
        return $this->methods;
        
    }
    
    final public function errors() {
        
        return $this->errors;
        
    }
    
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
