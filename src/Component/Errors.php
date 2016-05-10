<?php namespace Comodojo\RpcServer\Component;

/**
 * RPC errors manager
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

class Errors {

    /**
     * Array of capabilities
     *
     * @var array
     */
    private $rpc_errors = array();

    /**
     * Current logger
     *
     * @var \Psr\Log\LoggerInterface
     */
    private $logger = null;

    /**
     * Class constructor
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(\Psr\Log\LoggerInterface $logger) {

        $this->logger = $logger;

    }

    /**
     * Add an error
     *
     * @param int    $code
     * @param string $message
     *
     * @return bool
     */
    final public function add($code, $message) {

        if ( array_key_exists($code, $this->rpc_errors) ) {

            $this->logger->warning("Cannot add error ".$code.": duplicate entry");

            return false;

        } else {

            $this->rpc_errors[$code] = $message;

            $this->logger->info("Added error ".$code);

            return true;

        }

    }

    /**
     * Delete an error
     *
     * @param int $code
     *
     * @return bool
     */
    final public function delete($code) {

        if ( array_key_exists($code, $this->rpc_errors) ) {

            unset($this->rpc_errors[$code]);

            $this->logger->info("Deleted error ".$code);

            return true;

        } else {

            $this->logger->warning("Cannot delete error ".$code.": entry not found");

            return false;

        }

    }

    /**
     * Get registered error(s)
     *
     * @param int $code
     *
     * @return mixed
     */
    final public function get($code = null) {

        if ( is_null($code) ) return $this->rpc_errors;

        else if ( array_key_exists($code, $this->rpc_errors) ) return $this->rpc_errors[$code];

        else if ( $code >= -32099 && $code <= -32000 ) return 'Server Error';

        else return 'Unknown Error';

    }

}
