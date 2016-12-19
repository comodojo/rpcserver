<?php namespace Comodojo\RpcServer\Introspection;

use \Comodojo\RpcServer\Request\Parameters;
use \Comodojo\Exception\RpcException;

/**
 * The system.methodSignature method implementation
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

class MethodSignature {

    /**
     * Execute call
     *
     * @param Parameters $params
     *
     * @return array
     */
    final public static function execute(Parameters $params) {

        $asked_method = $params->get('method');

        $method = $params->methods()->get($asked_method);

        if ( is_null($method) ) throw new RpcException("Method not found", -32601);

        $signatures = $method->getSignatures();

        return sizeof($signatures) == 1 ? $signatures[0] : $signatures;

    }

}
