<?php namespace Comodojo\RpcServer\Request;

use \Comodojo\RpcServer\Request\Parameters;
use \Comodojo\RpcServer\RpcMethod;
use \Comodojo\Foundation\Validation\DataValidation as Validator;
use \Psr\Log\LoggerInterface;
use \Comodojo\Exception\RpcException;
use \Exception;

/**
 * The XMLRPC processor
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

class XmlProcessor {

    /**
     * Requested RPC method
     *
     * @var string
     */
    private $method;

    /**
     * A parameters object
     *
     * @var Parameters
     */
    private $parameters;

    /**
     * Selected method
     *
     * @var RpcMethod
     */
    private $registered_method;

    /**
     * Selected signature
     *
     * @var int
     */
    private $selected_signature;

    /**
     * Current logger
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Class constructor
     *
     * @param array $payload
     * @param Parameters $parameters
     * @param LoggerInterface $logger
     */
    public function __construct(array $payload, Parameters $parameters, LoggerInterface $logger) {

        $this->logger = $logger;

        $this->logger->debug("Starting XML processor");

        try {

            $this->parameters = $parameters;

            list($this->method, $request_parameters) = self::preprocessRequest($payload);

            $this->logger->debug("Current request", array(
                'METHOD' => $this->method,
                'PARAMS' => $request_parameters
            ));

            $this->registered_method = $this->checkRequestSustainability();

            $this->selected_signature = $this->checkRequestConsistence($request_parameters);

            $parameters = self::matchParameters($request_parameters, $this->registered_method, $this->selected_signature);

            $this->parameters->setParameters($parameters);

        } catch (RpcException $re) {

            $this->logger->warning($re->getMessage());

            throw $re;

        } catch (Exception $e) {

            $this->logger->error($e->getMessage());

            throw $e;

        }

    }

    /**
     * Run the processor and exec callback(s)
     *
     * @return mixed
     * @throws Exception
     */
    public function run() {

        $callback = $this->registered_method->getCallback();
        $attributes = $this->registered_method->getArguments();
        array_unshift($attributes, $this->parameters);

        set_error_handler(

            function($severity, $message, $file, $line) {

                $this->logger->error($message, array(
                    "FILE" => $file,
                    "LINE" => $line
                ));

                throw new RpcException('Internal error', -32603);

            }

        );

        try {

            // $return = call_user_func($callback, $this->parameters);
            $return = call_user_func_array($callback, $attributes);

        } catch (RpcException $re) {

            restore_error_handler();

            throw $re;

        } catch (Exception $e) {

            restore_error_handler();

            $this->logger->error($e->getMessage(), array(
                "FILE" => $e->getFile(),
                "LINE" => $e->getLine()
            ));

            throw new RpcException('Internal error', -32603);

        }

        restore_error_handler();

        return $return;

    }

    /**
     * Static constructor - start processor
     *
     * @param array $payload
     * @param Parameters $parameters
     * @param LoggerInterface $logger
     *
     * @return mixed
     * @throws RpcException
     * @throws Exception
     */
    public static function process(array $payload, Parameters $parameters, LoggerInterface $logger) {

        try {

            $processor = new self($payload, $parameters, $logger);

            $return = $processor->run();

        } catch (RpcException $re) {

            throw $re;

        } catch (Exception $e) {

            throw $e;

        }

        return $return;

    }

    /**
     * Check if a request is sustainable (i.e. if method is registered)
     *
     * @return RpcMethod
     * @throws RpcException
     */
    private function checkRequestSustainability() {

        $method = $this->parameters->methods()->get($this->method);

        if ( is_null($method) ) throw new RpcException("Method not found", -32601);

        return $method;

    }

    /**
     * Check if a request is consistent (i.e. if it matches one of method's signatures)
     *
     * @param array  $provided_parameters
     *
     * @return int
     * @throws RpcException
     */
    private function checkRequestConsistence(array $provided_parameters) {

        $signatures = $this->registered_method->getSignatures(false);

        $provided_parameters_count = count($provided_parameters);

        foreach ( $signatures as $num=>$signature ) {

            $requested_parameters_count = count($signature["PARAMETERS"]);

            if ( $provided_parameters_count != $requested_parameters_count ) {

                continue;

            }

            $index = 0;

            foreach ( $signature["PARAMETERS"] as $parameter => $type ) {

                if ( !Validator::validate($provided_parameters[$index], $type) ) continue 2;

                $index += 1;

            }

            return $num;

        }

        throw new RpcException("Invalid params", -32602);

    }

    /**
     * Create an associative array of $name => $parameter from current signature
     *
     * @param array $provided
     * @param RpcMethod $method
     * @param integer $selected_signature
     *
     * @return array
     */
    private static function matchParameters(array $provided, RpcMethod $method, $selected_signature) {

        $parameters = [];

        $requested_parameters = $method->selectSignature($selected_signature)->getParameters();

        $requested_parameters_keys = array_keys($requested_parameters);

        foreach ( $provided as $index => $parameter ) {

            $parameters[$requested_parameters_keys[$index]] = $parameter;

        }

        return $parameters;

    }

    /**
     * Preprocess a single xml request
     *
     * @param array $payload
     *
     * @return array
     */
    private static function preprocessRequest(array $payload) {

        return (is_array($payload[0])) ? array('system.multicall', array($payload)) : array($payload[0], $payload[1]);

    }

}
