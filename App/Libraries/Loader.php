<?php
/**
 * Loader class. This class will load anything for example: views, template, configuration file.
 * 
 * @package rd-fontawesome
 * @license http://opensource.org/licenses/MIT MIT
 * @since 1.0.0
 */


namespace RdFontAwesome\App\Libraries;


if (!class_exists('\\RdFontAwesome\\App\\Libraries\\Loader')) {
    class Loader
    {


        /**
         * @var \RdFontAwesome\App\App
         */
        public $App;


        /**
         * Automatic `require_once` all files in App/functions folder.
         */
        public function autoLoadFunctions()
        {
            $this_plugin_dir = dirname(RDFONTAWESOME_FILE);
            $di = new \RecursiveDirectoryIterator($this_plugin_dir . DIRECTORY_SEPARATOR . 'App' . DIRECTORY_SEPARATOR . 'functions', \RecursiveDirectoryIterator::SKIP_DOTS);
            $it = new \RecursiveIteratorIterator($di);
            unset($di);

            foreach ($it as $file) {
                if (is_file($file)) {
                    require_once $file;
                }
            }// endforeach;

            unset($file, $it, $this_plugin_dir);
        }// autoloadFunctions


        /**
         * Automatic look into those controllers and register to the main App class to make it works.
         * 
         * The controllers that will be register must extended `\RdFontAwesome\App\Controllers\BaseController`
         * that is implemented `\RdFontAwesome\App\Controllers\ControllerInterface` to have registerHooks() method in it, 
         * otherwise it will be skipped.
         */
        public function autoRegisterControllers()
        {
            $this_plugin_dir = dirname(RDFONTAWESOME_FILE);
            $di = new \RecursiveDirectoryIterator($this_plugin_dir . DIRECTORY_SEPARATOR . 'App' . DIRECTORY_SEPARATOR . 'Controllers', \RecursiveDirectoryIterator::SKIP_DOTS);
            $it = new \RecursiveIteratorIterator($di);
            unset($di);

            foreach ($it as $file) {
                $this_file_classname = '\\RdFontAwesome' . str_replace([$this_plugin_dir, '.php', '/'], ['', '', '\\'], $file);
                $testClass = new \ReflectionClass($this_file_classname);
                if (!$testClass->isAbstract() && class_exists($this_file_classname)) {
                    $ControllerClass = new $this_file_classname();
                    $ControllerClass->Loader = $this->App->Loader;
                    if (
                        $ControllerClass instanceof \RdFontAwesome\App\Controllers\BaseController && 
                        method_exists($ControllerClass, 'registerHooks')
                    ) {
                        $ControllerClass->registerHooks();
                    }
                    unset($ControllerClass);
                }
                unset($testClass, $this_file_classname);
            }// endforeach;

            unset($file, $it, $this_plugin_dir);
        }// autoRegisterControllers


        /**
         * Load views.
         * 
         * @param string $view_name view file name refer from app/Views folder.
         * @param array $data for send data variable to view.
         * @param bool $require_once use include or include_once? if true, use include_once.
         * @return bool return true if success loading, or return false if failed to load.
         */
        public function loadView(string $view_name, array $data = [], bool $require_once = false): bool
        {
            $view_dir = dirname(__DIR__) . '/Views/';

            if (
                $view_name != null && 
                is_file($view_dir . $view_name . '.php')
            ) {
                extract($data, EXTR_PREFIX_SAME, 'dupvar_');

                if ($require_once === true) {
                    include_once $view_dir . $view_name . '.php';
                } else {
                    include $view_dir . $view_name . '.php';
                }

                unset($view_dir);
                return true;
            }

            unset($view_dir);
            return false;
        }// loadView


    }// Loader
}