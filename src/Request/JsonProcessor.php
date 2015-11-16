<?php namespace Comodojo\RpcServer\Request;

use \Comodojo\RpcServer\Request\Parameters;
use \Comodojo\Exception\RpcException;
use \Exception;

/** 
 * The JSONRPC processor
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
 
class JsonProcessor {

    /**
     * A parameters object
     *
     * @var \Comodojo\RpcServer\Request\Parameters
     */
    private $parameters = null;
    
    /**
     * Array of requests
     *
     * @var array
     */
    private $requests = array();
    
    /**
     * Array of results
     *
     * @var array
     */
    private $results = array();

    /**
     * Internal flag to identify a batch request
     *
     * @var bool
     */
    private $is_batch_request = false;

    /**
     * Current logger
     *
     * @var \Psr\Log\LoggerInterface
     */
    private $logger = null;
    
    /**
     * Class constructor
     *
     * @param string                                 $payload
     * @param \Comodojo\RpcServer\Request\Parameters $parameters
     * @param \Psr\Log\LoggerInterface               $logger
     */
    public function __construct($payload, Parameters $parameters, \Psr\Log\LoggerInterface $logger) {

        $this->logger = $logger;

        $this->logger->notice("Starting JSON processor");
        
        $this->parameters = $parameters;

        list($this->is_batch_request, $this->requests) = self::preprocessJsonPayload($payload);
        
    }
    
    /**
     * Run the processor and exec callback(s)
     *
     * @return mixed
     * @throws Exception
     */
    public function run() {
        
        foreach ( $this->requests as $request ) {
            
            if ( isset($request['ERROR_CODE']) && isset($request['ERROR_MESSAGE']) ) {

                $this->logger->warning("Invalid request ".$request['ID']);
                
                $result = self::packJsonError($request['ERROR_CODE'], $request['ERROR_MESSAGE'], $request['ID']);

                if ( !is_null($result) ) $this->results[] = $result;
                
            } else {
                
                try {

                    $this->logger->notice("Serving request ".$request['METHOD']."(".$request['ID'].")");
                
                    $response = $this->runSingleRequest($request['METHOD'], $request['PARAMETERS']);
                    
                    $result = self::packJsonSuccess($response, $request['ID']);
                    
                } catch (RpcException $re) {

                    $this->logger->warning("Error handling request ".$request['ID'].": ".$re->getMessage());

                    $result = self::packJsonError($re->getCode(), $re->getMessage(), $request['ID']);
                    
                } catch (Exception $e) {

                    $this->logger->error($e->getMessage());
            
                    throw $e;
            
                }

                if ( !is_null($result) ) $this->results[] = $result;
                
            }
            

        } 
        
        if ( empty($this->results) ) {

            return null;

        } else if ( $this->is_batch_request ) {

            return $this->results;

        } else {

            return $this->results[0];

        }
        
    }
    
    /**
     * Static constructor - start processor
     *
     * @param string                                 $payload
     * @param \Comodojo\RpcServer\Request\Parameters $parameters
     * @param \Psr\Log\LoggerInterface               $logger
     * 
     * @return mixed
     * @throws Exception
     */
    public static function process($payload, Parameters $parameters, \Psr\Log\LoggerInterface $logger) {
    
        try {
            
            $processor = new JsonProcessor($payload, $parameters, $logger);
            
            $return = $processor->run();
            
        } catch (Exception $e) {
            
            throw $e;
            
        }

        return $return;
        
    }

    /**
     * Preprocess json payload
     *
     * @param string $payload
     * 
     * @return array
     */
    private static function preprocessJsonPayload($payload) {
        
        $requests = array();

        $is_batch = false;
        
        if ( is_array($payload) ) {

            $is_batch = true;
            
            foreach ( $payload as $request ) $requests[] = self::preprocessJsonRequest($request);
            
        } else {
            
            $requests[] = self::preprocessJsonRequest($payload);
            
        }
        
        return array($is_batch, $requests);
        
    }
    
    /**
     * Preprocess a single json request
     *
     * @param array $request
     * 
     * @return array
     */
    private static function preprocessJsonRequest($request) {
        
        // check for required parameters
        
        if (
            !is_object($request) ||
            !property_exists($request, 'jsonrpc') ||
            !property_exists($request, 'method') ||
            $request->jsonrpc != '2.0' || 
            empty($request->method)
        ) {
            
            return array(
                'ERROR_CODE' => -32600,
                'ERROR_MESSAGE' => 'Invalid Request',
                'ID' => !isset($request['id']) ? null : $request['id']
            );
            
        }
        
        // parse request's components
        
        return array(
            'METHOD' => $request->method,
            'PARAMETERS' => property_exists($request, 'params') ? $request->params : array(),
            'ID' => property_exists($request, 'id') ? $request->id : null
        );
        
    }
    
    /**
     * Exec a single request
     *
     * @param string $request
     * @param array  $parameters
     * 
     * @return mixed
     * @throws RpcException
     */
    private function runSingleRequest($request_method, $parameters) {
        
        try {
            
            $registered_method = $this->checkRequestSustainability($request_method);
            
            $selected_signature = $this->checkRequestConsistence($registered_method, $parameters);
            
            if ( is_array($parameters) ) $parameters = self::matchParameters($parameters, $registered_method, $selected_signature);
            
            $this->parameters->setParameters($parameters);
            
            $callback = $registered_method->getCallback();
        
            $method = $registered_method->getMethod();
            
        } catch (RpcException $re) {
            
            throw $re;
            
        }
        
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
        
            $return = empty($method) ? call_user_func($callback, $this->parameters) : call_user_func(array($callback, $method), $this->parameters);

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
     * Pack a json error response
     *
     * @param integer $code
     * @param string  $message
     * @param integer $id
     * 
     * @return array|null
     */
    private static function packJsonError($code, $message, $id) {
        
        if ( !is_null($id) ) {
                
            return array(
                'jsonrpc' => '2.0',
                'error' => array(
                    'code' => $code,
                    'message' => $message
                ),
                'id' => $id
            );
        
        } else {

            return null;

        }
        
    }
    
    /**
     * Pack a json success response
     *
     * @param mixed   $result
     * @param integer $id
     * 
     * @return array
     */
    private static function packJsonSuccess($result, $id) {
        
        if ( !is_null($id) ) {
                
            return array(
                'jsonrpc' => '2.0',
                'result' => $result,
                'id' => $id
            );
        
        } else {

            return null;

        }
        
    }
    
    /**
     * Create an associative array of $name => $parameter from current signature
     *
     * @param array   $provided
     * @param srting  $method
     * @param integer $selected_signature
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
     * Check if a request is sustainable (i.e. if method is registered)
     *
     * @param string $request_method
     * 
     * @return \Comodojo\RpcServer\RpcMethod
     * @throws \Comodojo\Exception\RpcException
     */
    private function checkRequestSustainability($request_method) {
        
        $method = $this->parameters->methods()->get($request_method);
        
        if ( is_null($method) ) throw new RpcException("Method not found", -32601);
        
        return $method;
        
    }
    
    /**
     * Check if a request is consistent (i.e. if it matches one of method's signatures)
     *
     * @param string $registered_method
     * @param array  $parameters
     * 
     * @return int
     * @throws \Comodojo\Exception\RpcException
     */
    private function checkRequestConsistence($registered_method, $parameters) {

        $signatures = $registered_method->getSignatures(false);

        foreach ( $signatures as $num => $signature ) {
            
            if ( self::checkSignatureMatch($parameters, $signature["PARAMETERS"]) === true ) return $num;

        }

        throw new RpcException("Invalid params", -32602);
        
    }

    /**
     * Check if call match a signature
     *
     * @param array  $provided
     * @param array  $requested
     * 
     * @return bool
     */
    private static function checkSignatureMatch($provided, $requested) {

        if ( is_object($provided) ) {

            foreach ( $provided as $parameter=>$value ) {
            
                if ( !isset($requested[$parameter]) ) return false;

            }    

        } else {

            $provided_parameters_count = count($provided);

            $requested_parameters_count = count($requested);

            if ( $provided_parameters_count != $requested_parameters_count ) return false;

        }

        return true;

    }
    
}
