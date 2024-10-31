<?php
/**
 * Activation class is the class that will be working on activate, deactivate, delete WordPress plugin.
 * 
 * @package rundiz-oauth
 * @license http://opensource.org/licenses/MIT MIT
 * @since 1.0.0
 */


namespace RdFontAwesome\App\Controllers\Admin;


if (!class_exists('\\RdFontAwesome\\App\\Controllers\\Activation')) {
    class Activation extends \RdFontAwesome\App\Controllers\BaseController
    {


        /**
         * add links to plugin actions area
         * 
         * @param array $actions current plugin actions. (including deactivate, edit).
         * @param string $plugin_file the plugin file for checking.
         * @return array return modified links
         */
        public function actionLinks(array $actions, string $plugin_file): array
        {
            static $plugin;
            
            if (!isset($plugin)) {
                $plugin = plugin_basename(RDFONTAWESOME_FILE);
            }
            
            if ($plugin == $plugin_file) {
                $link['settings'] = '<a href="'.  esc_url(get_admin_url(null, 'options-general.php?page=rd-fontawesome-settings')).'">'.__('Settings').'</a>';
                $actions = array_merge($link, $actions);
            }
            
            return $actions;
        }// actionLinks


        /**
         * {@inheritDoc}
         */
        public function registerHooks()
        {
            // register uninstall hook. this hook will be work on delete plugin.
            // * register uninstall hook MUST be static method or function.
            register_uninstall_hook(RDFONTAWESOME_FILE, ['\\RdFontAwesome\\App\\Controllers\\Admin\\Activation', 'uninstall']);

            // add filter action links. this will be displayed in actions area of plugin page. for example: xxxbefore | Activate | Edit | Delete | xxxafter
            add_filter('plugin_action_links', [$this, 'actionLinks'], 10, 5);
        }// registerHooks


        /**
         * delete the plugin.
         * 
         * @global \wpdb $wpdb
         * @global \WP_Filesystem_Base $wp_filesystem
         */
        public static function uninstall()
        {
            // do something that will be happens on delete plugin.
            global $wpdb, $wp_filesystem;
            WP_Filesystem();
            $wpdb->show_errors();

            // delete Font Awesome files in publish path.
            $thisClass = static::getInstance();
            $wp_filesystem->delete(($thisClass->getStaticPluginData())['targetPublishDir'], true);

            // delete option
            delete_option(\RdFontAwesome\App\Libraries\Settings::OPTION_NAME);
        }// uninstall


    }
}