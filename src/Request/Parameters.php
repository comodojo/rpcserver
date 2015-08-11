<?php namespace Comodojo\RpcServer\Request;

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
    
    public function setParameters($parameters) {
        
        $this->parameters = $parameters;
        
        return $this;
        
    }
    
    public function setCapabilities($capabilities) {
        
        $this->capabilities = $capabilities;
        
        return $this;
        
    }
    
    public function setMethods($methods) {
        
        $this->methods = $methods;
        
        return $this;
        
    }
    
    public function setErrors($errors) {
        
        $this->errors = $errors;
        
        return $this;
        
    }
    
    public function get($parameter) {
        
        if ( array_key_exists($parameter, $this->parameters) ) return $this->parameters[$parameter];
        
        else return null;
        
    }
    
    public function getParameters() {
        
        return $this->parameters;
        
    }
    
    public function getMethods() {
        
        return $this->methods;
        
    }
    
    public function getMethod($method) {
        
        if ( array_key_exists($method, $this->methods) ) return $this->methods[$method];
        
        else return null;
        
    }
    
    public function getCapabilities() {
        
        return $this->capabilities;
        
    }
    
}
