<?php
/**
 * Plugin Name: Rundiz Font Awesome
 * Plugin URI: https://rundiz.com/?p=319
 * Description: Use Font Awesome from your host and update from GitHub.
 * Version: 1.0.3
 * Requires at least: 5.0
 * Requires PHP: 7.0
 * Author: Vee Winch
 * Author URI: https://rundiz.com
 * License: MIT
 * License URI: http://opensource.org/licenses/MIT
 * Text Domain: rd-fontawesome
 * Domain Path: /App/languages
 */


// define this plugin main file path.
if (!defined('RDFONTAWESOME_FILE')) {
    define('RDFONTAWESOME_FILE', __FILE__);
}


// include this plugin's autoload.
require __DIR__.'/autoload.php';


// initialize plugin app main class.
$this_plugin_app = new \RdFontAwesome\App\App();
$this_plugin_app->run();
unset($this_plugin_app);