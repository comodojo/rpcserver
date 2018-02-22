<?php namespace Comodojo\RpcServer\Request;

use \Comodojo\RpcServer\Component\Capabilities;
use \Comodojo\RpcServer\Component\Methods;
use \Comodojo\RpcServer\Component\Errors;
use \Psr\Log\LoggerInterface;

/**
 * The parameters object
 *
 * It provides to each RPC method a way to access:
 *  - Provided parameters
 *  - Supported capabilities
 *  - Implemented methods
 *  - Predefined errors
 *  - Current RPC protocol
 *  - Current logger
 *
 * The parameter object is the only one parameter passed to a RPC method's implementation
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

class Parameters {

    /**
     * Array of provided parameters
     *
     * @var array
     */
    private $parameters = [];

    /**
     * Supported capabilities
     *
     * @var Capabilities
     */
    private $capabilities;

    /**
     * Implemented methods
     *
     * @var Methods
     */
    private $methods;

    /**
     * Predefined errors
     *
     * @var Errors
     */
    private $errors;

    /**
     * Current RPC protocol (json|rpc)
     *
     * @var string
     */
    private $protocol;

    /**
     * Current logger
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Class constructor
     *
     * @param Capabilities $capabilities
     * @param Methods $methods
     * @param Errors $errors
     * @param LoggerInterface $logger
     * @param string $protocol
     */
    public function __construct(
        Capabilities $capabilities,
        Methods $methods,
        Errors $errors,
        LoggerInterface $logger,
        $protocol
    ) {

        $this->capabilities = $capabilities;

        $this->methods = $methods;

        $this->errors = $errors;

        $this->logger = $logger;

        $this->protocol = $protocol;

    }

    /**
     * Set provided parameters
     *
     * @param array $parameters
     *
     * @return Parameters
     */
    final public function setParameters(array $parameters = []) {

        $this->parameters = $parameters;

        return $this;

    }

    /**
     * Get capabilities object
     *
     * @deprecated
     * @see Parameters::getCapabilities()
     * @return Capabilities
     */
    public function capabilities() {

        return $this->getCapabilities();

    }

    /**
     * Get capabilities object
     *
     * @deprecated
     * @see Parameters::getCapabilities()
     * @return Capabilities
     */
    public function getCapabilities() {

        return $this->capabilities;

    }

    /**
     * Get methods object
     *
     * @deprecated
     * @see Parameters::getMethods()
     * @return Methods
     */
    public function methods() {

        return $this->getMethods();

    }

    /**
     * Get methods object
     *
     * @return Methods
     */
    public function getMethods() {

        return $this->methods;

    }

    /**
     * Get errors object
     *
     * @deprecated
     * @see Parameters::getErrors()
     * @return Errors
     */
    public function errors() {

        return $this->getErrors();

    }

    /**
     * Get errors object
     *
     * @return Errors
     */
    public function getErrors() {

        return $this->errors;

    }

    /**
     * Get current RPC protocol
     *
     * @deprecated
     * @see Parameters::getProtocol()
     * @return string
     */
    public function protocol() {

        return $this->getProtocol();

    }

    /**
     * Get current RPC protocol
     *
     * @return string
     */
    public function getProtocol() {

        return $this->protocol;

    }

    /**
     * Get current logger instance
     *
     * @deprecated
     * @see Parameters::getLogger()
     * @return LoggerInterface
     */
    public function logger() {

        return $this->getLogger();

    }

    /**
     * Get current logger instance
     *
     * @return LoggerInterface
     */
    public function getLogger() {

        return $this->logger;

    }

    /**
     * Get parameter(s)
     *
     * @param string $parameter (optional) The parameter name (null will return whole array of parameters)
     *
     * @return mixed
     */
    public function get($parameter = null) {

        if ( empty($parameter) ) {

            $return = $this->parameters;

        } else if ( array_key_exists($parameter, $this->parameters) ) {

            $return = $this->parameters[$parameter];

        } else {

            $return = null;

        }

        return $return;

    }

}
