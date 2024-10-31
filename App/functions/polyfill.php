<?php
/** 
 * Polyfill functions.
 * 
 * @package rd-fontawesome
 * @license http://opensource.org/licenses/MIT MIT
 * @since 1.0.0
 */


if (!function_exists('array_key_first')) {
    /**
     * @link https://www.php.net/manual/en/function.array-key-first.php Original source code.
     * @param array $arr
     * @return mixed
     */
    function array_key_first(array $arr) {
        foreach($arr as $key => $unused) {
            return $key;
        }
        return NULL;
    }
}