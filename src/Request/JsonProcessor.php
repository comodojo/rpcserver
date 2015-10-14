<?php namespace Comodojo\RpcServer\Request;

use \Comodojo\RpcServer\Request\Parameters;
use \Comodojo\Exception\RpcException;
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
 
class JsonProcessor {

    private $parameters;
    
    private $requests = array();
    
    private $results = array();

    private $is_batch_request = false;
    
    public function __construct($payload, Parameters $parameters) {
        
        $this->parameters = $parameters;

        list($this->is_batch_request, $this->requests) = self::preprocessJsonPayload($payload);
        
    }
    
    public function run() {
        
        foreach ( $this->requests as $request ) {
            
            if ( isset($request['ERROR_CODE']) && isset($request['ERROR_MESSAGE']) ) {
                
                $result = self::packJsonError($request['ERROR_CODE'], $request['ERROR_MESSAGE'], $request['ID']);

                if ( !is_null($result) ) $this->results[] = $result;
                
            } else {
                
                try {
                
                    $response = $this->runSingleRequest($request['METHOD'], $request['PARAMETERS']);
                    
                    $result = self::packJsonSuccess($response, $request['ID']);
                    
                } catch (RpcException $re) {

                    $result = self::packJsonError($re->getCode(), $re->getMessage(), $request['ID']);
                    
                } catch (Exception $e) {
            
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
    
    public static function process($payload, Parameters $parameters) {
    
        try {
            
            $processor = new JsonProcessor($payload, $parameters);
            
            $return = $processor->run();
            
        } catch (RpcException $re) {
            
            throw $re;
            
        } catch (Exception $e) {
            
            throw $e;
            
        }

        return $return;
        
    }

    private static function preprocessJsonPayload($payload) {
        
        $requests = array();

        $is_batch = false;
        
        if ( is_array($payload) ) {

            $is_batch = true;
            
            foreach($payload as $request) $requests[] = self::preprocessJsonRequest($request);
            
        } else {
            
            $requests[] = self::preprocessJsonRequest($payload);
            
        }
        
        return array($is_batch, $requests);
        
    }
    
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

                throw new RpcException('Internal error', -32603);

            }

        );
        
        try {
        
            $return = empty($method) ? call_user_func($callback, $this->parameters) : call_user_func(Array($callback, $method), $this->parameters);

        } catch (RpcException $re) {
            
            restore_error_handler();

            throw $re;
            
        } catch (Exception $e) {

            restore_error_handler();

            throw new RpcException('Internal error', -32603);
            
        }

        restore_error_handler();
        
        return $return;
        
    }
    
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
    
    private static function packJsonSuccess($result, $id) {
        
        if ( !is_null($id) ) {
                
            return array(
                'jsonrpc' => '2.0',
                'result' => $result,
                'id' => $id
            );
        
        }
        
    }
    
    private static function matchParameters($provided, $method, $selected_signature) {
        
        $parameters = array();
        
        $requested_parameters = $method->selectSignature($selected_signature)->getParameters();
        
        $requested_parameters_keys = array_keys($requested_parameters);

        foreach( $provided as $index => $parameter ) {
            
            $parameters[$requested_parameters_keys[$index]] = $parameter;
            
        }
        
        return $parameters;
        
    }
    
    private function checkRequestSustainability($request_method) {
        
        $method = $this->parameters->methods()->get($request_method);
        
        if ( is_null($method) ) throw new RpcException("Method not found", -32601);
        
        return $method;
        
    }
    
    private function checkRequestConsistence($registered_method, $parameters) {

        $signatures = $registered_method->getSignatures(false);

        foreach ($signatures as $num => $signature) {
            
            if ( self::checkSignatureMatch($parameters, $signature["PARAMETERS"]) === true ) return $num;

        }

        throw new RpcException("Invalid params", -32602);
        
    }

    private static function checkSignatureMatch($provided, $requested) {

        if ( is_object($provided) ) {

            foreach ($provided as $parameter=>$value) {
            
                if ( !isset($requested[$parameter]) ) return false;

            }    

        } else {

            $provided_parameters_count = count( $provided );

            $requested_parameters_count = count( $requested );

            if ( $provided_parameters_count != $requested_parameters_count ) return false;

        }

        return true;

    }
    
}