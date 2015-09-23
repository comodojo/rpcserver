<?php namespace Comodojo\RpcServer\Component;

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
 
class Methods {

    private $methods = array();
    
    final public function add(\Comodojo\RpcServer\RpcMethod $method) {
        
        $name = $method->getName();

        if ( array_key_exists($name, $this->methods) ) {
            
            return false;
            
        } else {
            
            $this->methods[$name] = $method;
            
            return true;
            
        }
        
    }
    
    final public function delete($name) {
        
        if ( array_key_exists($name, $this->methods) ) {
            
            unset($this->methods[$name]);
            
            return true;
            
        } else {
            
            return false;
            
        }
        
    }
    
    final public function get($method = null) {
        
        if ( empty($method) ) {
            
            $return = $this->methods;
            
        } else if ( array_key_exists($method, $this->methods) ) {
            
            $return = $this->methods[$method];
        
        } else {
            
            $return = null;
            
        }
        
        return $return;
        
    }
    
}
