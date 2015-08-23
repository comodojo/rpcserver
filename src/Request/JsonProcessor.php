<?php namespace Comodojo\RpcServer\Request;

use \Comodojo\RpcServer\Request\Parameters;
use \Comodojo\Exception\RpcException;

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
    
    public function __construct($payload, Parameters $parameters) {
        
        $this->parameters = $parameters;
        
        $this->requests = self::preprocessJsonPayload($payload);
        
    }
    
    public function run() {
        
        foreach ( $this->requests as $request ) {
            
            if ( isset($request['ERROR_CODE']) && isset($request['ERROR_MESSAGE']) ) {
                
                self::packJsonError($request['ERROR_CODE'], $request['ERROR_MESSAGE'], $request['ID']);
                
            } else {
                
                try {
                
                    $response = $this->runSingleRequest($request['METHOD'], $request['PARAMETERS']);
                    
                    self::packJsonSuccess($response, $request['ID']);
                    
                } catch (RpcException $re) {
                    
                    self::packJsonError($re->getCode(), $re->getMessage(), $request['ID']);
                    
                } catch (Exception $e) {
            
                    throw $e;
            
                }
                
            }
            

        } 
        
        return $this->results;
        
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
        
        if ( is_array($payload) ) {
            
            foreach($payload as $request) $requests[] = self::preprocessJsonRequest($request);
            
        } else {
            
            $requests[] = self::preprocessJsonRequest($payload);
            
        }
        
        return $requests;
        
    }
    
    private static function preprocessJsonRequest($request) {
        
        // check for required parameters
        
        if (
            !isset($request['jsonrpc']) || 
            !isset($request['method']) ||  
            $request['jsonrpc'] != '2.0' || 
            empty($request['method'])
        ) {
            
            return array(
                'ERROR_CODE' => -32600,
                'ERROR_MESSAGE' => 'Invalid Request',
                'ID' => !isset($request['id']) ? null : $request['id']
            );
            
        }
        
        // parse request's components
        
        return array(
            'METHOD' => $request['method'],
            'PARAMETERS' => !isset($request['params']) ? array() : $request['params'],
            'ID' => !isset($request['id']) ? null : $request['id']
        );
        
    }
    
    private function runSingleRequest($request_method, $parameters) {
        
        try {
            
            $registered_method = $this->checkRequestSustainability($requested_method);
            
            $this->checkRequestConsistence($registered_method, $parameters);
            
            if ( is_array($parameters) ) $parameters = self::matchParameters($parameters, $registered_method);
            
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
            
        }

        restore_error_handler();
        
        return $return;
        
    }
    
    private static function packJsonError($code, $message, $id) {
        
        if ( !is_null($id) ) {
                
            $this->results[] = array(
                'jsonrpc' => '2.0',
                'error' => array(
                    'code' => $code,
                    'message' => $message
                ),
                'id' => $id
            );
        
        }
        
    }
    
    private static function packJsonSuccess($result, $id) {
        
        if ( !is_null($id) ) {
                
            $this->results[] = array(
                'jsonrpc' => '2.0',
                'result' => $result,
                'id' => $id
            );
        
        }
        
    }
    
    private static function matchParameters($provided, $method) {
        
        $parameters = array();
        
        $requested_parameters = $method->getParameters('NUMERIC');
        
        foreach( $provided as $index => $parameter ) {
            
            $parameters[$requested_parameters[$index]] = $parameter;
            
        }
        
        return $parameters;
        
    }
    
    private function checkRequestSustainability($request_method) {
        
        $method = $this->parameters->methods->get($request_method);
        
        if ( is_null($method) ) throw new RpcException("Method not found", -32601);
        
        return $method;
        
    }
    
    private function checkRequestConsistence($registered_method, $parameters) {
        
        if ( is_array($parameters) ) {
            
            $requested_parameters = $registered_method->getParameters();
            
            foreach( $parameters as $parameter => $value ) {
                
                if ( !isset($requested_parameters[$parameter]) ) throw new RpcException("Invalid params", -32602);
                
            }
            
        } else {
            
            $requested_parameters = $registered_method->getParameters('NUMERIC');
            
            $provided_parameters_count = count($parameters->get());
            
            $requested_parameters_count = count( $requested_parameters );
            
            if ( $provided_parameters_count != $requested_parameters_count ) throw new RpcException("Invalid params", -32602);
            
        }
        
    }
    
}