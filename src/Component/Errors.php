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
 
class Errors {

    private $rpc_errors = array(
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
    
    final public function add($code, $message) {
        
        if ( array_key_exists($code, $this->rpc_errors) ) {
            
            return false;
            
        } else {
            
            $this->rpc_errors[$code] = $message;
            
            return true;
            
        }
        
    }
    
    final public function delete($code) {
        
        if ( array_key_exists($code, $this->rpc_errors) ) {
            
            unset($this->rpc_errors[$code]);
            
            return true;
            
        } else {
            
            return false;
            
        }
        
    }
    
    final public function get($code) {
        
        if ( array_key_exists($code, $this->rpc_errors) ) return $this->rpc_errors[$code];
        
        else if ( $code >= -32099 &&  $code <= -32000 ) return 'Server Error';
        
        else return 'Unknown Error';
        
    }
    
}
