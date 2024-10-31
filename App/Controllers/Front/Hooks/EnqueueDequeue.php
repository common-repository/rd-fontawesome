<?php
/**
 * Handle enqueue/dequeue styles and scripts.
 * 
 * @package rd-fontawesome
 * @license http://opensource.org/licenses/MIT MIT
 * @since 1.0.0
 */


namespace RdFontAwesome\App\Controllers\Front\Hooks;


if (!class_exists('\\RdFontAwesome\\App\\Controllers\\Front\\Hooks\\EnqueueDequeue')) {
    class EnqueueDequeue extends \RdFontAwesome\App\Controllers\BaseController
    {


        /**
         * Get all settings.
         * 
         * @return array
         */
        protected function getSettings(): array
        {
            $Settings = new \RdFontAwesome\App\Libraries\Settings();
            $allSettings = $Settings->getAllSettings();
            unset($Settings);
            return $allSettings;
        }// getSettings


        /**
         * Dequeue scripts.
         */
        public function dequeueScripts()
        {
            $allSettings = $this->getSettings();

            if (isset($allSettings['dequeue_js']) && !empty($allSettings['dequeue_js'])) {
                $FAScan = new \RdFontAwesome\App\Libraries\FAScan();
                list($newHashCss, $newHashJs) = $FAScan->setHashNames($allSettings['dequeue_css'], $allSettings['dequeue_js']);
                if ($newHashJs !== $allSettings['dequeue_hashed'][1]) {
                    // if hashed check and saved mismatched. do not dequeue.
                    return ;
                }
                unset($FAScan, $newHashCss, $newHashJs);

                $handles = explode(',', $allSettings['dequeue_js']);
                foreach ($handles as $handle) {
                    wp_dequeue_script(trim($handle));
                }// endforeach;
                unset($handle, $handles);
            }
            unset($allSettings);
        }// dequeueScripts


        /**
         * Dequeue styles.
         */
        public function dequeueStyles()
        {
            $allSettings = $this->getSettings();

            if (isset($allSettings['dequeue_css']) && !empty($allSettings['dequeue_css'])) {
                $FAScan = new \RdFontAwesome\App\Libraries\FAScan();
                list($newHashCss, $newHashJs) = $FAScan->setHashNames($allSettings['dequeue_css'], $allSettings['dequeue_js']);
                if ($newHashCss !== $allSettings['dequeue_hashed'][0]) {
                    // if hashed check and saved mismatched. do not dequeue.
                    return ;
                }
                unset($FAScan, $newHashCss, $newHashJs);

                $handles = explode(',', $allSettings['dequeue_css']);
                foreach ($handles as $handle) {
                    wp_dequeue_style(trim($handle));
                }// endforeach;
                unset($handle, $handles);
            }
            unset($allSettings);
        }// dequeueStyles


        /**
         * Enqueue this plugin's assets.
         */
        public function enqueueAssets()
        {
            $allSettings = $this->getSettings();

            if (!isset($allSettings['donot_enqueue']) || $allSettings['donot_enqueue'] !== '1') {
                $faVersion = ($allSettings['fontawesome_version'] ?? false);
                if (false === $faVersion) {
                    // if not yet installed.
                    return ;
                }

                $majorVersion = ($allSettings['major_version'] ?? ($this->getStaticPluginData())['defaultMajorVersion']);
                $Url = new \RdFontAwesome\App\Libraries\Url($this->getStaticPluginData());
                $asset = $Url->getRequiredAssetsToEnqueue($majorVersion);
                unset($majorVersion, $Url);

                if (isset($asset['css']) && is_array($asset['css'])) {
                    $i = 0;
                    foreach ($asset['css'] as $css) {
                        wp_enqueue_style('rd-fontawesome' . ($i > 0 ? $i : ''), $css, [], $faVersion);
                        $i++;
                    }// endforeach;
                    unset($css, $i);
                }
                unset($asset, $faVersion);
            }
            unset($allSettings);
        }// enqueueAssets


        /**
         * {@inheritDoc}
         */
        public function registerHooks()
        {
            $allSettings = $this->getSettings();

            if (isset($allSettings['dequeue_css']) && !empty($allSettings['dequeue_css'])) {
                add_action('wp_enqueue_scripts', [$this, 'dequeueStyles'], 100);
            }
            if (isset($allSettings['dequeue_js']) && !empty($allSettings['dequeue_js'])) {
                add_action('wp_enqueue_scripts', [$this, 'dequeueScripts'], 100);
            }
            if ((!isset($allSettings['donot_enqueue']) || $allSettings['donot_enqueue'] !== '1') && !empty($allSettings)) {
                add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
            }
            unset($allSettings);
        }// registerHooks


    }// EnqueueDequeue
}
