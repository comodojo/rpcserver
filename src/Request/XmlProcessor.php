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
 
class XmlProcessor {

    private $method;
    
    private $payload;
    
    private $parameters;
    
    private $registered_method;

    private $selected_signature = null;
    
    public function __construct($payload, Parameters $parameters) {
        
        try {
        
            $this->parameters = $parameters;
        
            list($this->method, $request_parameters) = self::preprocessRequest($payload);

            $this->registered_method = $this->checkRequestSustainability();
            
            $this->selected_signature = $this->checkRequestConsistence($request_parameters);
            
            $parameters = self::matchParameters($request_parameters, $this->registered_method, $this->selected_signature);
            
            $this->parameters->setParameters($parameters);
        
        } catch (RpcException $re) {
            
            throw $re;
            
        } catch (Exception $e) {
            
            throw $e;
            
        }
        
    }
    
    public function run() {
        
        $callback = $this->registered_method->getCallback();
        
        $method = $this->registered_method->getMethod();
        
        set_error_handler( 

            function($severity, $message, $file, $line) {

                throw new RpcException('Internal error', -32603);

            }

        );

        try {
        
            $return = empty($method) ? call_user_func($callback, $this->parameters) : call_user_func(Array($callback, $method), $this->parameters);

        } catch (RpcException $re) {

            throw $re;
            
        }

        restore_error_handler();
        
        return $return;
        
    }
    
    public static function process($payload, Parameters $parameters) {
    
        try {
            
            $processor = new self($payload, $parameters);
            
            $return = $processor->run();
            
        } catch (RpcException $re) {
            
            throw $re;
            
        } catch (Exception $e) {
            
            throw $e;
            
        }

        return $return;
        
    }

    private function checkRequestSustainability() {
        
        $method = $this->parameters->methods()->get($this->method);
        
        if ( is_null($method) ) throw new RpcException("Method not found", -32601);
        
        return $method;
        
    }
    
    private function checkRequestConsistence($provided_parameters) {

        $signatures = $this->registered_method->getSignatures(false);

        $provided_parameters_count = count($provided_parameters);

        foreach ($signatures as $num=>$signature) {
            
            $requested_parameters = array_values($signature["PARAMETERS"]);

            $requested_parameters_count = count( $requested_parameters );

            if ( $provided_parameters_count == $requested_parameters_count ) return $num;

        }

        throw new RpcException("Invalid params", -32602);
        
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
    
    private static function preprocessRequest($payload) {
        
        return ( is_array($payload[0]) ) ? array('system.multicall', $payload[0]) : array($payload[0], $payload[1]);
        
    }
    
}