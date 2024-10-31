<?php
/**
 * Scan for deuqueue handles on front pages.
 * 
 * @package rd-fontawesome
 * @license http://opensource.org/licenses/MIT MIT
 * @since 1.0.0
 */


namespace RdFontAwesome\App\Controllers\Front\Hooks;


if (!class_exists('\\RdFontAwesome\\App\\Controllers\\Front\\Hooks\\PrintScriptsScanDequeue')) {
    class PrintScriptsScanDequeue extends \RdFontAwesome\App\Controllers\BaseController
    {


        /**
         * {@inheritDoc}
         */
        public function registerHooks()
        {
            if (get_transient($this->scanDequeueTransientName) === 'true' && !is_admin()) {
                // if there is a task to scan dequeue handles and not in admin pages.
                $nonceValue = get_transient('rd-fontawesome-nonce');
                if (isset($_REQUEST['nonce']) && $_REQUEST['nonce'] === $nonceValue) {
                    // if nonce matched.
                    delete_transient('rd-fontawesome-nonce');
                    add_action('wp_print_scripts', [$this, 'scanDequeueHandles']);
                }
                unset($nonceValue);
            }
        }// registerHooks


        /**
         * Scan for dequeue handles that must be Font Awesome only.
         */
        public function scanDequeueHandles()
        {
            $didScan = get_transient($this->scanDequeueDidScannedTransientName);
            if ($didScan === 'true') {
                // if already scanned, no need to do it again. let user back to admin page and check the result.
                return ;
            }
            unset($didScan);

            $handles = get_transient($this->scanDequeueHandlesTransientName);
            if (false === $handles || empty($handles)) {
                // if handle names to scan is not found.
                delete_transient($this->scanDequeueHandlesTransientName);
                return ;
            }
            list($cssHandles, $jsHandles) = json_decode($handles);
            unset($handles);

            $FAScan = new \RdFontAwesome\App\Libraries\FAScan();
            $cssHandles = $FAScan->doScan($cssHandles);
            $jsHandles = $FAScan->doScan($jsHandles, 'js');
            $hashed = $FAScan->setHashNames($cssHandles, $jsHandles);
            unset($FAScan);

            set_transient($this->scanDequeueDidScannedTransientName, 'true', $this->scanDequeueTransientExpires);
            set_transient($this->scanDequeueHandlesResultTransientName, json_encode([$cssHandles, $jsHandles]), $this->scanDequeueTransientExpires);
            set_transient($this->scanDequeueHandlesHashedResultTransientName, $hashed, $this->scanDequeueTransientExpires);
            unset($cssHandles, $hashed, $jsHandles);

            wp_register_script('rd-fontawesome-frontscandequeue', plugin_dir_url(RDFONTAWESOME_FILE) . 'assets/js/front/scan-dequeue.js', [], false, true);
            wp_localize_script(
                'rd-fontawesome-frontscandequeue',
                'RdFontAwesomeFrontScanDequeueObj',
                [
                    'txtScanCompleted' => __('Scan completed. Please close this window, go back to settings page and click on save button again.', 'rd-fontawesome'),
                ]
            );
            wp_enqueue_script('rd-fontawesome-frontscandequeue');
        }// scanDequeueHandles


    }// PrintScriptsScanDequeue
}
