<?php namespace Comodojo\RpcServer\Introspection;

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
 
class GetCapabilities {

    final public static function execute($system, $params) {
        
        return $system->getCapabilities();
        
    }

//    protected static function getXmlrpcCapabilities() {
//       return array(
//            'xmlrpc' => array(
//                'specUrl' => 'http://www.xmlrpc.com/spec',
//                'specVersion' => 1
//            ),
//            'system.multicall' => array(
//                'specUrl' => 'http://www.xmlrpc.com/discuss/msgReader$1208',
//                'specVersion' => 1
//            ),
//            'introspection' => array(
//                'specUrl' => 'http://phpxmlrpc.sourceforge.net/doc-2/ch10.html',
//                'specVersion' => 2,
//            ),
//            'nil' => array(
//                'specUrl' => 'http://www.ontosys.com/xml-rpc/extensions.php',
//                'specVersion' => 1
//            ),
//            'faults_interop' => array(
//                'specUrl' => 'http://xmlrpc-epi.sourceforge.net/specs/rfc.fault_codes.php',
//                'specVersion' => 20010516
//            )
//        );
//    }

}
