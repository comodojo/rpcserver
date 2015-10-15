<?php namespace Comodojo\RpcServer;

use \Exception;

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
    
    private $signatures = array();

    private $current_signature = null;

    public function __construct($name, $callback, $method = null) {
        
        if ( !is_string($name) ) throw new Exception("RPC method exception: invalid or undefined name");
        
        if ( !self::checkIfCallable($callback, $method) ) throw new Exception("RPC method exception, invalid or undefined callback");
        
        $this->name = $name;
        
        $this->callback = $callback;
        
        $this->method = $method;

        $this->addSignature();
        
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
    
    public function getDescription() {
        
        return $this->description;
        
    }

    public function addSignature() {

        $signature = array(
            "PARAMETERS" => array(),
            "RETURNTYPE" => 'undefined'
        );

        array_push($this->signatures, $signature);

        $this->current_signature = max(array_keys($this->signatures));

        return $this;

    }

    public function getSignatures($compact = true) {

        if ( $compact ) {

            $signatures = array();

            foreach ( $this->signatures as $signature ) {
                
                $signatures[] = array_merge(array($signature["RETURNTYPE"]), array_values($signature["PARAMETERS"]));

            }

            return $signatures;

        } else {

            return $this->signatures;

        }

    }

    public function getSignature($compact = true) {

        if ( $compact ) {

            return array_merge(array($this->signatures[$this->current_signature]["RETURNTYPE"]), array_values($this->signatures[$this->current_signature]["PARAMETERS"]));

        } else {

            return $this->signatures[$this->current_signature];

        }
        
    }

    public function deleteSignature($signature) {

        if ( !is_integer($signature) || !isset($this->signatures[$signature]) ) {

            throw new Exception("RPC method exception: invalid signature reference");

        }

        unset($this->signatures[$signature]);

        return true;

    }

    public function selectSignature($signature) {

        if ( !is_integer($signature) || !isset($this->signatures[$signature]) ) {

            throw new Exception("RPC method exception: invalid signature reference");

        }

        $this->current_signature = $signature;

        return $this;

    }
    
    public function setReturnType($type) {
        
        if ( !in_array($type, self::$rpcvalues) ) throw new Exception("RPC method exception: invalid return type");
        
        $this->signatures[$this->current_signature]["RETURNTYPE"] = $type;

        return $this;
        
    }
    
    public function getReturnType() {
        
        return $this->signatures[$this->current_signature]["RETURNTYPE"];
        
    }
    
    public function addParameter($type, $name) {
        
        if ( !in_array($type, self::$rpcvalues) ) throw new Exception("RPC method exception: invalid parameter type");
        
        if ( empty($name) ) throw new Exception("RPC method exception: invalid parameter name");
        
        $this->signatures[$this->current_signature]["PARAMETERS"][$name] = $type;

        return $this;
        
    }
    
    public function deleteParameter($name) {
        
        if ( !array_key_exists($name, $this->signatures[$this->current_signature]["PARAMETERS"]) ) throw new Exception("RPC method exception: cannot find parameter");
        
        unset($this->signatures[$this->current_signature]["PARAMETERS"][$name]);
        
        return $this;
        
    }
    
    public function getParameters($method = 'ASSOC') {
        
        if ( $method == 'NUMERIC' ) return array_values($this->signatures[$this->current_signature]["PARAMETERS"]);
        
        else return $this->signatures[$this->current_signature]["PARAMETERS"];
        
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
