<?php namespace Comodojo\RpcServer\Reserved;

use \Comodojo\RpcServer\RpcServer;
use \Comodojo\RpcServer\Request\Parameters;
use \Comodojo\RpcServer\Request\XmlProcessor;
use \Comodojo\Exception\RpcException;
use \Exception;

/**
 * The system.multicall method implementation.
 *
 * This method is available ONLY for XMLRPC protocol; json v2 SHOULD use batch requests.
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

class Multicall {

    /**
     * Execute call
     *
     * @param Parameters $params
     *
     * @return array
     * @throws RpcException
     */
    final public static function execute(Parameters $params) {

        if ( $params->protocol() != RpcServer::XMLRPC ) {

            throw new RpcException($params->errors()->get(-31000), -31000);

        }

        $boxcarred_requests = $params->get('requests');

        $results = [];

        foreach ( $boxcarred_requests as $position => $request ) {

            $new_parameters = new Parameters(
                $params->capabilities(),
                $params->methods(),
                $params->errors(),
                $params->logger(),
                $params->protocol()
            );

            $results[$position] = self::singleCall($request, $new_parameters);

        }

        return $results;

    }

    /**
     * Perform a single call
     *
     * @param array $request
     * @param Parameters $parameters_object
     *
     * @return mixed
     */
    private static function singleCall(array $request, Parameters $parameters_object) {

        if ( !isset($request[0]) || !isset($request[1]) ) {

            return self::packError(-32600, $parameters_object->errors()->get(-32600));

        }

        if ( $request[0] == 'system.multicall' ) {

            return self::packError(-31001, $parameters_object->errors()->get(-31001));

        }

        $payload = array($request[0], $request[1]);

        try {

            return XmlProcessor::process($payload, $parameters_object, $parameters_object->logger());

        } catch (RpcException $re) {

            return self::packError($re->getCode(), $re->getMessage());

        } catch (Exception $e) {

            return self::packError(-32500, $re->getMessage());

        }

    }

    /**
     * Pack an XMLRPC error
     *
     * @param integer $code
     * @param string  $message
     *
     * @return mixed
     */
    private static function packError($code, $message) {

        return array('faultCode' => $code, 'faultString' => $message);

    }

}
