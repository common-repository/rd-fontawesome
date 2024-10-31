<?php
/**
 * String class.
 * 
 * @package rd-fontawesome
 * @license http://opensource.org/licenses/MIT MIT
 * @since 1.0.0
 */


namespace RdFontAwesome\App\Libraries;


if (!class_exists('\\RdFontAwesome\\App\\Libraries\\Strings')) {
    class Strings
    {


        /**
         * Normalize handles string by reformat xx, yyy ,  zzz to be xxx,yyy,zzz.
         * 
         * @param string $handles
         * @return string
         */
        public function normalizeHandlesString(string $handles): string
        {
            $pattern = '(\s*,\s*)';
            return preg_replace('/' . $pattern . '/m', ',', $handles);
        }// normalizeHandlesString


    }
}
