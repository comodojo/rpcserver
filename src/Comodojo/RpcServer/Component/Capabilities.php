<?php namespace Comodojo\RpcServer\Component;

use \Psr\Log\LoggerInterface;

/**
 * RPC capabilities manager
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

class Capabilities {

    /**
     * Array of capabilities
     *
     * @var array
     */
    private $rpc_capabilities = [];

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
     * Add a capability
     *
     * @param string $capability
     * @param string $specUrl
     * @param string $specVersion
     *
     * @return bool
     */
    final public function add($capability, $specUrl, $specVersion) {

        if ( array_key_exists($capability, $this->rpc_capabilities) ) {

            $this->logger->warning("Cannot add capability $capability: duplicate entry");

            return false;

        } else {

            $this->rpc_capabilities[$capability] = array(
                'specUrl' => $specUrl,
                'specVersion' => $specVersion
            );

            $this->logger->debug("Added capability $capability");

            return true;

        }

    }

    /**
     * Delete a capability
     *
     * @param string $capability
     *
     * @return bool
     */
    final public function delete($capability) {

        if ( array_key_exists($capability, $this->rpc_capabilities) ) {

            unset($this->rpc_capabilities[$capability]);

            $this->logger->debug("Deleted capability $capability");

            return true;

        } else {

            $this->logger->warning("Cannot delete capability $capability: entry not found");

            return false;

        }

    }

    /**
     * Get registered capability (capabilities)
     *
     * @param string $capability
     *
     * @return array|null
     */
    final public function get($capability = null) {

        if ( is_null($capability) ) return $this->rpc_capabilities;

        else if ( array_key_exists($capability, $this->rpc_capabilities) ) return $this->rpc_capabilities[$capability];

        else return null;

    }

}
