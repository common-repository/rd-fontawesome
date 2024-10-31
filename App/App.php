<?php
/**
 * @package rd-fontawesome
 * @license http://opensource.org/licenses/MIT MIT
 * @since 1.0.0
 */


namespace RdFontAwesome\App;


if (!class_exists('\\RdFontAwesome\App\\App')) {
    class App
    {


        use AppTrait;


        /**
         * load text domain. (language files)
         */
        public function loadLanguage()
        {
            load_plugin_textdomain('rd-fontawesome', false, dirname(plugin_basename(RDFONTAWESOME_FILE)) . '/App/languages/');
        }// loadLanguage


        /**
         * Run the application.
         */
        public function run()
        {
            add_action('plugins_loaded', function() {
                // @link https://codex.wordpress.org/Function_Reference/load_plugin_textdomain Reference.
                // @link https://developer.wordpress.org/reference/functions/load_plugin_textdomain/ Reference.
                // @link https://wordpress.stackexchange.com/questions/245250/override-plugin-text-domain-in-child-theme Override text domain, translation by other themes, plugins.
                // load language of this plugin.
                $this->loadLanguage();
            });

            // Initialize the loader class.
            $this->Loader = new \RdFontAwesome\App\Libraries\Loader();
            $this->Loader->App = $this;
            $this->Loader->autoLoadFunctions();
            $this->Loader->autoRegisterControllers();
        }// run


    }
}
