<?php namespace Comodojo\RpcServer;

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
 
class RpcMethod {
    
    public static $rpcvalues = array(
        "i4",
        "int",
        "double",
        "boolean",
        "base64",
        "dateTime.iso8601",
        "string",
        "array",
        "struct",
        "nil",
        "ex:nil",
        "undefined"
    );
    
    private $name = null;
    
    private $callback = null;
    
    private $method = null;
    
    private $description = null;
    
    private $parameters = array();
    
    private $return_type = 'undefined';

    public function __construct($name, $callback, $method = null) {
        
        if ( !is_string($name) ) throw new Exception("RPC method exception: invalid or undefined name");
        
        if ( !self::checkIfCallable($callback, $method) ) throw new Exception("RPC method exception, invalid or undefined callback");
        
        $this->name = $name;
        
        $this->callback = $callback;
        
        $this->method = $method;
        
    }
    
    public function getName() {
        
        return $this->name;
        
    }
    
    public function getCallback() {
        
        return $this->callback;
        
    }
    
    public function getMethod() {
        
        return $this->method;
        
    }
    
    public function setDescription($description = null) {
        
        if ( empty($description) ) $this->description = null;
        
        else if ( !is_string($description) ) throw new Exception("RPC method exception: invalid description");
        
        else $this->description = $description;
        
        return $this;
        
    }
    
    public function getDescription($description) {
        
        return $this->description;
        
    }
    
    public function setReturnType($type) {
        
        if ( !in_array($type, self::$rpcvalues) ) throw new Exception("RPC method exception: invalid return type");
        
        $this->return_type = $type;
        
        return $this;
        
    }
    
    public function getReturnType() {
        
        return $this->return_type;
        
    }
    
    public function addParameter($type, $name) {
        
        if ( !in_array($type, self::$rpcvalues) ) throw new Exception("RPC method exception: invalid parameter type");
        
        if ( empty($name) ) throw new Exception("RPC method exception: invalid parameter name");
        
        $this->parameters[$parameter] = $type;
        
        return $this;
        
    }
    
    public function deleteParameter($name) {
        
        if ( !in_array($name, $this->parameters) ) throw new Exception("RPC method exception: cannot find parameter");
        
        unset($this->parameters[$name]);
        
        return $this;
        
    }
    
    public function getParameters($method = 'ASSOC') {
        
        if ( $method == 'NUMERIC' ) return array_values($this->parameters);
        
        else return $this->parameters;
        
    }
    
    public function getSignature() {
        
        $signature = array($this->return_type);
        
        foreach($this->parameters as $parameter => $type) $signature[] = $type;
        
        return $signature;
        
    }
    
    public static function create($name, $callback, $method = null) {
        
        try {
            
            $method = new RpcMethod($name, $callback, $method);
            
        } catch (Exception $e) {
            
            throw $e;
            
        }
        
        return $method;
        
    }
    
    private static function checkIfCallable($callback, $method) {
        
        return empty($method) ? is_callable($callback) : is_callable($callback, $method);
        
    }
    
}
