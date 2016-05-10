<?php namespace Comodojo\RpcServer\Util;

/**
 * Data validation layer.
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

class DataValidator {

    /**
     * Generic data validator.
     *
     * @param string $kind
     * @param mixed  $value
     *
     * @return bool
     */
    public static function validate($kind, $value) {

        $subfunction = "self::validate".ucfirst(strtolower(str_replace(".","",$kind)));

        return call_user_func($subfunction, $value);

    }

    public static function validateInt($value) {

        return is_integer($value);

    }

    public static function validateDouble($value) {

        return is_float($value);

    }

    public static function validateBoolean($value) {

        return is_bool($value);

    }

    public static function validateBase64($value) {

        return is_string($value);

    }

    public static function validateString($value) {

        return is_string($value);

    }

    public static function validateNull($value) {

        return is_null($value);

    }

    public static function validateUndefined($value) {

        return true;

    }

    public static function validateArray($value) {

        if ( !is_array($value) ) return false;

        return ( array() === $value || !self::validateStruct($value) );

    }

    public static function validateStruct($value) {

        if ( is_array($value) || is_object($value) ) {

            $array = (array) $value;

            return array_keys($array) !== range(0, count($array) - 1);

        }

        return false;

    }

    public static function validateDatetimeiso8601($value) {

        $match = preg_match('/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})/', $value, $matches);

        if ( $match ) {

            $time = gmmktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);

            $timestring = strtotime($value);

            if ($timestring === false) return false;

            return $timestring == $time;

        }

        return false;

    }

}
