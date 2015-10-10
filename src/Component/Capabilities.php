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
 
class Capabilities {

    private $rpc_capabilities = array();
    
    final public function add($capability, $specUrl, $specVersion) {
        
        if ( array_key_exists($capability, $this->rpc_capabilities) ) {
            
            return false;
            
        } else {
            
            $this->rpc_capabilities[$capability] = array(
                'specUrl' => $specUrl,
                'specVersion' => $specVersion
            );
            
            return true;
            
        }
        
    }
    
    final public function delete($capability) {
        
        if ( array_key_exists($capability, $this->rpc_capabilities) ) {
            
            unset($this->rpc_capabilities[$capability]);
            
            return true;
            
        } else {
            
            return false;
            
        }
        
    }
    
    final public function get() {
        
        return $this->rpc_capabilities;
        
    }
    
}
