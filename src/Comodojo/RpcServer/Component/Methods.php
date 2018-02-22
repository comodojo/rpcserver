<?php namespace Comodojo\RpcServer\Component;

use \Psr\Log\LoggerInterface;
use \Comodojo\RpcServer\RpcMethod;

/**
 * RPC methods manager
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

class Methods {

    /**
     * Array of methods
     *
     * @var array
     */
    private $rpc_methods = [];

    /**
     * Current logger
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Class constructor
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger) {

        $this->logger = $logger;

    }

    /**
     * Add an RPC method
     *
     * @param RpcMethod $method
     *
     * @return bool
     */
    final public function add(RpcMethod $method) {

        $name = $method->getName();

        if ( array_key_exists($name, $this->rpc_methods) ) {

            $this->logger->warning("Cannot add method $name: duplicate entry");

            return false;

        } else {

            $this->rpc_methods[$name] = $method;

            $this->logger->debug("Added method $name");

            return true;

        }

    }

    /**
     * Delete a method
     *
     * @param string $name
     *
     * @return bool
     */
    final public function delete($name) {

        if ( array_key_exists($name, $this->rpc_methods) ) {

            unset($this->rpc_methods[$name]);

            $this->logger->debug("Deleted method $name");

            return true;

        } else {

            $this->logger->warning("Cannot delete method $name: entry not found");

            return false;

        }

    }

    /**
     * Get registered method(s)
     *
     * @param string $method
     *
     * @return array|RpcMethod|null
     * @TODO Verify null return in case of missing methods and, in case, handle the error condition
     */
    final public function get($method = null) {

        if ( empty($method) ) {

            $return = $this->rpc_methods;

        } else if ( array_key_exists($method, $this->rpc_methods) ) {

            $return = $this->rpc_methods[$method];

        } else {

            $return = null;

        }

        return $return;

    }

}
