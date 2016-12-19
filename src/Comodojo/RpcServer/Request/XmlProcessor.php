<?php namespace Comodojo\RpcServer\Request;

use \Comodojo\RpcServer\Request\Parameters;
use \Comodojo\Foundation\Validation\DataValidation as Validator;
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
     * @var \Comodojo\RpcServer\Request\Parameters
     */
    private $parameters = null;

    /**
     * Selected method
     *
     * @var \Comodojo\RpcServer\RpcMethod
     */
    private $registered_method = null;

    /**
     * Selected signature
     *
     * @var int
     */
    private $selected_signature = null;

    /**
     * Current logger
     *
     * @var \Psr\Log\LoggerInterface
     */
    private $logger = null;

    /**
     * Class constructor
     *
     * @param array                                  $payload
     * @param \Comodojo\RpcServer\Request\Parameters $parameters
     * @param \Psr\Log\LoggerInterface               $logger
     */
    public function __construct($payload, Parameters $parameters, \Psr\Log\LoggerInterface $logger) {

        $this->logger = $logger;

        $this->logger->notice("Starting XML processor");

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
     * @param array                                  $payload
     * @param \Comodojo\RpcServer\Request\Parameters $parameters
     * @param \Psr\Log\LoggerInterface               $logger
     *
     * @return mixed
     * @throws \Comodojo\Exception\RpcException
     * @throws Exception
     */
    public static function process($payload, Parameters $parameters, \Psr\Log\LoggerInterface $logger) {

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
     * @return \Comodojo\RpcServer\RpcMethod
     * @throws \Comodojo\Exception\RpcException
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
     * @throws \Comodojo\Exception\RpcException
     */
    private function checkRequestConsistence($provided_parameters) {

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
     * @param array                         $provided
     * @param \Comodojo\RpcServer\RpcMethod $method
     * @param integer                       $selected_signature
     *
     * @return array
     */
    private static function matchParameters($provided, $method, $selected_signature) {

        $parameters = array();

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
    private static function preprocessRequest($payload) {

        return (is_array($payload[0])) ? array('system.multicall', array($payload)) : array($payload[0], $payload[1]);

    }

}
