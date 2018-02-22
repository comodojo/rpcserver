<?php namespace Comodojo\RpcServer;

use \InvalidArgumentException;
use \Exception;

/**
 * RPC Method object
 *
 * It create a method's object ready to inject into RpcServer.
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

class RpcMethod {

    const FETCH_ASSOC = 'ASSOC';

    const FETCH_NUMERIC = "NUMERIC";

    /**
     * Generic-to-RPC values map
     *
     * @var array $rpcvalues
     */
    public static $rpcvalues = array(
        "i4" => "int",
        "int" => "int",
        "integer" => "int",
        "double" => "double",
        "float" => "double",
        "boolean" => "boolean",
        "bool" => "boolean",
        "base64" => "base64",
        "dateTime.iso8601" => "dateTime.iso8601",
        "datetime" => "dateTime.iso8601",
        "string" => "string",
        "array" => "array",
        "struct" => "struct",
        "nil" => "null",
        "ex:nil" => "null",
        "null" => "null",
        "undefined" => "undefined"
    );

    /**
     * Name of method
     *
     * @var string
     */
    private $name;

    /**
     * Callback class|function
     *
     * @var callable
     */
    private $callback;

    /**
     * Description of method
     *
     * @var string
     */
    private $description;

    /**
     * Array of supported signatures
     *
     * @var array
     */
    private $signatures = [];

    /**
     * Internal pointer to current signature
     *
     * @var int
     */
    private $current_signature;

    /**
     * Placeholder for additional arguments
     *
     * @var array
     */
    private $arguments = [];

    /**
     * Class constructor
     *
     * @param string $name
     * @param callable  $callback
     *
     * @throws InvalidArgumentException
     */
    public function __construct($name, callable $callback, ...$arguments) {

        if ( empty($name) ) throw new InvalidArgumentException("Invalid or undefined RpcMethod name");

        if ( !is_callable($callback) ) throw new InvalidArgumentException("Invalid or undefined RpcMethod callback");

        $this->name = $name;

        $this->callback = $callback;

        $this->arguments = $arguments;

        $this->addSignature();

    }

    /**
     * Get the method's name
     *
     * @return string
     */
    public function getName() {

        return $this->name;

    }

    /**
     * Get the method's callback
     *
     * @return callable
     */
    public function getCallback() {

        return $this->callback;

    }

    /**
     * Set the method's description
     *
     * @param string $description
     *
     * @return self
     * @throws InvalidArgumentException
     */
    public function setDescription($description = null) {

        if ( empty($description) ) $this->description = null;

        else if ( !is_string($description) ) throw new InvalidArgumentException("Invalid RpcMethod description");

        else $this->description = $description;

        return $this;

    }

    /**
     * Get the method's method
     *
     * @return string|null
     */
    public function getDescription() {

        return $this->description;

    }

    /**
     * Get additional arguments to forward to callback
     *
     * @return array
     */
    public function getArguments() {

        return $this->arguments;

    }

    /**
     * Add a signature and switch internal pointer
     *
     * @return self
     */
    public function addSignature() {

        $signature = [
            "PARAMETERS" => [],
            "RETURNTYPE" => 'undefined'
        ];

        array_push($this->signatures, $signature);

        $this->current_signature = max(array_keys($this->signatures));

        return $this;

    }

    /**
     * Get the method's signatures
     *
     * @param bool $compact (default) Compact signatures in a format compatible with system.methodSignature
     *
     * @return array
     */
    public function getSignatures($compact = true) {

        if ( $compact ) {

            $signatures = [];

            foreach ( $this->signatures as $signature ) {

                $signatures[] = array_merge([$signature["RETURNTYPE"]], array_values($signature["PARAMETERS"]));

            }

            return $signatures;

        } else {

            return $this->signatures;

        }

    }

    /**
     * Get the current method's signature
     *
     * @param bool $compact (default) Compact signatures in a format compatible with system.methodSignature
     *
     * @return array
     */
    public function getSignature($compact = true) {

        if ( $compact ) {

            return array_merge([$this->signatures[$this->current_signature]["RETURNTYPE"]], array_values($this->signatures[$this->current_signature]["PARAMETERS"]));

        } else {

            return $this->signatures[$this->current_signature];

        }

    }

    /**
     * Delete a signature
     *
     * @param integer $signature The signature's ID
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public function deleteSignature($signature) {

        if ( !is_integer($signature) || !isset($this->signatures[$signature]) ) {

            throw new InvalidArgumentException("Invalid RpcMethod signature reference");

        }

        unset($this->signatures[$signature]);

        return true;

    }

    /**
     * Select a signature
     *
     * @param integer $signature The signature's ID
     *
     * @return self
     * @throws Exception
     */
    public function selectSignature($signature) {

        if ( !is_integer($signature) || !isset($this->signatures[$signature]) ) {

            throw new InvalidArgumentException("Invalid RpcMethod signature reference");

        }

        $this->current_signature = $signature;

        return $this;

    }

    /**
     * Set the current signature's return type
     *
     * @param string $type
     *
     * @return self
     * @throws InvalidArgumentException
     */
    public function setReturnType($type) {

        if ( !in_array($type, self::$rpcvalues) ) throw new InvalidArgumentException("Invalid RpcMethod return type");

        $this->signatures[$this->current_signature]["RETURNTYPE"] = self::$rpcvalues[$type];

        return $this;

    }

    /**
     * Get the current signature's return type
     *
     * @return string
     */
    public function getReturnType() {

        return $this->signatures[$this->current_signature]["RETURNTYPE"];

    }

    /**
     * Add a parameter to current signature
     *
     * @param string $type
     * @param string $name
     *
     * @return self
     * @throws InvalidArgumentException
     */
    public function addParameter($type, $name) {

        if ( !in_array($type, self::$rpcvalues) ) throw new InvalidArgumentException("Invalid type for parameter $name");

        if ( empty($name) ) throw new InvalidArgumentException("Missing parameter name");

        $this->signatures[$this->current_signature]["PARAMETERS"][$name] = self::$rpcvalues[$type];

        return $this;

    }

    /**
     * Delete a parameter from current signature
     *
     * @param string $name
     *
     * @return self
     * @throws Exception
     */
    public function deleteParameter($name) {

        if ( !array_key_exists($name, $this->signatures[$this->current_signature]["PARAMETERS"]) ) throw new Exception("Cannot find parameter $name");

        unset($this->signatures[$this->current_signature]["PARAMETERS"][$name]);

        return $this;

    }

    /**
     * Get current signature's parameters
     *
     * @param string $format The output array format (ASSOC|NUMERIC)
     *
     * @return array
     */
    public function getParameters($format = self::FETCH_ASSOC) {

        if ( $format === self::FETCH_NUMERIC ) return array_values($this->signatures[$this->current_signature]["PARAMETERS"]);

        else return $this->signatures[$this->current_signature]["PARAMETERS"];

    }

    /**
     * Static class constructor - create an RpcMethod object
     *
     * @param string $name
     * @param string $callback
     *
     * @return RpcMethod
     * @throws Exception
     */
    public static function create($name, callable $callback, ...$arguments) {

        try {

            return  new RpcMethod($name, $callback, ...$arguments);

        } catch (Exception $e) {

            throw $e;

        }

    }

}
