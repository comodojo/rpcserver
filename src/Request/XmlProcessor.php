<?php namespace Comodojo\RpcServer\Request;

use \Comodojo\RpcServer\Request\Parameters;

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
    
    public function __construct($payload, Parameters $parameters) {
        
        try {
        
            $this->parameters = $parameters;
        
            list($this->method, $request_parameters) = self::preprocessRequest($payload);
            
            $this->parameters->setParameters($request_parameters);
            
            $this->registered_method = $this->checkRequestSustainability();
            
            $this->checkRequestConsistence();
        
        } catch (RpcException $re) {
            
            throw $re;
            
        } catch (Exception $e) {
            
            throw $e;
            
        }
        
    }
    
    public function run() {
        
        $callback = $this->registered_method->getCallback();
        
        $method = $this->registered_method->getMethod();
        
        $return = empty($method) ? call_user_func($callback, $this->parameters) : call_user_func(Array($callback, $method), $this->parameters);
        
        return $return;
        
    }
    
    public static function process($payload, Parameters $parameters) {
    
        try {
            
            $processor = new Processor($payload, $parameters);
            
            $return = $processor->run();
            
        } catch (RpcException $re) {
            
            throw $re;
            
        } catch (Exception $e) {
            
            throw $e;
            
        }

        return $return;
        
    }

    private function checkRequestSustainability() {
        
        $method = $this->parameters->methods->get($this->method);
        
        if ( is_null($method) ) throw new RpcException("Method not found", -32601);
        
        return $method;
        
    }
    
    private function checkRequestConsistence() {
        
        $requested_parameters = $this->registered_method->getParameters('NUMERIC');
        
        $requested_parameters_count = count( $requested_parameters_count );
        
        $provided_parameters_count = count($this->parameters->get());
        
        if ( $provided_parameters_count != $requested_parameters_count ) throw new RpcException("Invalid params", -32602);
        
    }
    
    private static function preprocessRequest($payload) {
        
        return ( is_array($payload[0]) ) ? array('system.multicall', $payload[0]) : array($payload[0], $payload[1]);
        
    }
    
}