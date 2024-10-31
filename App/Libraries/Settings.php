<?php
/**
 * Settings class.
 * 
 * @package rd-fontawesome
 * @license http://opensource.org/licenses/MIT MIT
 * @since 1.0.0
 */


namespace RdFontAwesome\App\Libraries;


if (!class_exists('\\RdFontAwesome\\App\\Libraries\\Settings')) {
    class Settings
    {


        /**
         * @var string The main option name of this plugin.
         */
        const OPTION_NAME = 'rd-fontawesome';


        /**
         * Class constructor.
         */
        public function __construct()
        {
        }// __construct


        /**
         * Get all settings.
         * 
         * @return array
         */
        public function getAllSettings(): array
        {
            $output = [];

            $options = get_option(static::OPTION_NAME);
            if (is_array($options)) {
                $output = $options;
            }
            unset($options);

            return $output;
        }// getAllSettings


        /**
         * Save settings.
         * 
         * @param array $settings The array key => value pair of settings. Example:
         *      <pre>array(
         *          'download_type' => 'github',
         *          'fontawesome_version' => '5.14.5',
         *      )</pre>
         * @return bool
         */
        public function saveSettings(array $settings): bool
        {
            $allSettings = $this->getAllSettings();

            if (!array_key_exists('last_update', $settings)) {
                $settings['last_update'] = get_date_from_gmt(gmdate('Y-m-d H:i:s'));
            }
            if (!array_key_exists('last_update_gmt', $settings)) {
                $settings['last_update_gmt'] = gmdate('Y-m-d H:i:s');;
            }

            $allSettings = array_merge($allSettings, $settings);
            return update_option(static::OPTION_NAME, $allSettings);
        }// saveSettings


    }// Settings
}
