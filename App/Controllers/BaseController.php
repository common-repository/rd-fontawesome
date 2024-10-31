<?php
/**
 * Controller based.
 * 
 * @package rd-fontawesome
 * @license http://opensource.org/licenses/MIT MIT
 * @since 1.0.0
 */


namespace RdFontAwesome\App\Controllers;


if (!class_exists('\\RdFontAwesome\\App\\Controllers\\BaseController')) {
    abstract class BaseController implements ControllerInterface
    {


        use \RdFontAwesome\App\AppTrait;


        /**
         * Magic set.
         * 
         * @param string $name
         * @param mixed $value
         */
        public function __set(string $name, $value)
        {
            $allowedNames = ['Loader'];

            if (in_array($name, $allowedNames) && property_exists($this, $name)) {
                $this->{$name} = $value;
            }
        }// __set


    }// BaseController
}
