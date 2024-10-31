<?php
/**
 * AJAX for settings page.
 * 
 * @package rd-fontawesome
 * @license http://opensource.org/licenses/MIT MIT
 * @since 1.0.0
 */


namespace RdFontAwesome\App\Controllers\Admin;


if (!class_exists('\\RdFontAwesome\\App\\Controllers\\Admin\\SettingsAjax')) {
    class SettingsAjax extends \RdFontAwesome\App\Controllers\BaseController
    {


        /**
         * @var \\RdFontAwesome\\App\\Libraries\\Url
         */
        protected $Url;


        /**
         * Class constructor.
         */
        public function __construct()
        {
            $this->Url = new \RdFontAwesome\App\Libraries\Url($this->getStaticPluginData());
        }// __construct


        /**
         * Receive input major version, check if exists in available - if not then use default.
         * 
         * @return int.
         */
        protected function inputMajorVersion(): int
        {
            $major_version = (int) sanitize_text_field(filter_input(INPUT_POST, 'major_version', FILTER_SANITIZE_NUMBER_INT));
            if (!in_array($major_version, ($this->getStaticPluginData())['majorVersions'])) {
                // if selected major version is not in the list.
                // use default.
                $major_version = ($this->getStaticPluginData())['defaultMajorVersion'];
            }
            return $major_version;
        }// inputMajorVersion


        /**
         * Download and install latest Font Awesome version.
         */
        public function installLatestFAVersion()
        {
            // check permission.
            if (!current_user_can('manage_options')) {
                wp_die(__('You do not have permission to access this page.'));
                exit();
            }

            $output = [];
            check_ajax_referer('rdfontawesome_ajaxnonce', 'nonce');
            $personalToken = sanitize_text_field(filter_input(INPUT_POST, 'ghpersonalaccesstoken'));
            $major_version = $this->inputMajorVersion();

            $output = array_merge($output, $this->Url->retrieveLatestVersion($personalToken, $major_version));
            $output['tagVersion'] = sanitize_text_field($output['tagVersion']);
            $output['downloadLink'] = esc_url_raw($output['downloadLink']);
            $latestVersion = $output['tagVersion'];
            $downloadLink = $output['downloadLink'];

            if (isset($output['rateLimitRemaining']) && $output['rateLimitRemaining'] <= 0) {
                $limitExceeded = true;
                $statusCode = 403;
                $output['formResult'] = 'error';
                $output['formResultMessage'] = [
                    __('Your request rate limit on GItHub has been exceeded.', 'rd-fontawesome'),
                ];
            }

            if (
                is_string($downloadLink) && 
                !empty($downloadLink) && 
                is_string($latestVersion) &&
                !empty($latestVersion) &&
                (
                    !isset($limitExceeded) ||
                    $limitExceeded !== true
                )
            ) {
                // if found download link and latest version info and not GitHub API limit exceeded.
                $statusCode = 200;
                $Settings = new \RdFontAwesome\App\Libraries\Settings();
                $allSettings = $Settings->getAllSettings();

                if (
                    empty($allSettings) || 
                    (
                        isset($allSettings['fontawesome_version']) &&
                        version_compare($allSettings['fontawesome_version'], $latestVersion, '<')
                    ) ||
                    !is_dir(($this->getStaticPluginData())['targetPublishDir'])
                ) {
                    // if never installed before OR older than latest released.
                    $dlResult = $this->Url->downloadFile($downloadLink, $major_version);
                    $output['downloadResult'] = $this->Url->downloadResult;
                    if (is_wp_error($dlResult)) {
                        // if download has error occur.
                        $statusCode = 500;
                        $output['formResult'] = 'error';
                        $output['formResultMessage'] = [];
                        foreach ($dlResult->get_error_messages() as $eMessage) {
                            $output['formResultMessage'][] = $eMessage;
                        }// endforeach;
                        unset($eMessage);
                    } else {
                        // if download has no error (but maybe soft errors).
                        $output['downloadResult'] = $dlResult;
                        if (false === $dlResult) {
                            // if there are some soft errors but can continue.
                            $output['tempDir'] = $this->Url->tempDir;
                            $output['tempDirExists'] = (is_string($this->Url->tempDir) ? is_dir($this->Url->tempDir) : false);
                            $output['formResult'] = 'warning';
                            $output['formResultMessage'] = [
                                __('You have installed latest version of Font Awesome. However, there are some files or directories that is unable to move to target path.', 'rd-fontawesome') .
                                '<br>' .
                                (isset($output['downloadResult']['move']['failedValidatedMove']) ? implode(',', $output['downloadResult']['move']['failedValidatedMove']) : ''),
                            ];
                        }// endif soft error.

                        // save latest version to DB.
                        $Settings->saveSettings([
                            'major_version' => $major_version,
                            'fontawesome_version' => $latestVersion,
                        ]);
                        $output['allSettings'] = $Settings->getAllSettings();

                        if (!isset($output['formResult'])) {
                            $output['formResult'] = 'success';
                            $output['formResultMessage'] = [
                                __('Success! You have installed latest version of Font Awesome.', 'rd-fontawesome'),
                            ];
                        }
                    }// endif download has error or not.
                    unset($dlResult);
                } else {
                    // if already installed and is using latest version.
                    $output['skippedDownload'] = [
                        'result' => true,
                        'latestVersion' => $latestVersion,
                        'currentVersion' => ($allSettings['fontawesome_version'] ?? null),
                        'alreadyLatest' => true,
                    ];
                    $output['downloadLink'] = $downloadLink;
                    $output['tagVersion'] = ($allSettings['fontawesome_version'] ?? null);
                    $output['formResult'] = 'success';
                    $output['formResultMessage'] = [
                        __('Success! Your Font Awesome is already latest version.', 'rd-fontawesome'),
                    ];
                }

                unset($Settings);
            } else {
                // if download link is not found or latest version info is not found or limit exceeded (already set the errors message).
                if (!isset($statusCode)) {
                    $statusCode = 404;
                }
                if (!isset($output['formResult'])) {
                    $output['formResult'] = 'error';
                }
                if (!isset($output['formResultMessage'])) {
                    $output['formResultMessage'] = [];
                }
                $output['formResultMessage'][] = __('Unable to retrieve latest version from GitHub.', 'rd-fontawesome');
            }

            unset($downloadLink, $latestVersion, $limitExceeded, $major_version, $personalToken);
            wp_send_json($output, $statusCode);
        }// installLatestFAVersion


        /**
         * {@inheritDoc}
         */
        public function registerHooks()
        {
            if (is_admin()) {
                add_action('wp_ajax_rdfontawesome_installlatestversion', [$this, 'installLatestFAVersion']);
                add_action('wp_ajax_rdfontawesome_retrievelatestversion', [$this, 'retrieveLatestFAVersion']);
                add_action('wp_ajax_rdfontawesome_savesettings', [$this, 'saveSettings']);
                add_action('wp_ajax_rdfontawesome_testghpersonalaccesstoken', [$this, 'testPersonalAccessToken']);
                add_action('wp_ajax_rdfontawesome_uninstallfontawesome', [$this, 'uninstallFA']);
            }
        }// registerHooks


        /**
         * Retrieve latest Font Awesome version.
         */
        public function retrieveLatestFAVersion()
        {
            // check permission.
            if (!current_user_can('manage_options')) {
                wp_die(__('You do not have permission to access this page.'));
                exit();
            }

            $output = [];
            check_ajax_referer('rdfontawesome_ajaxnonce', 'nonce');
            $personalToken = sanitize_text_field(filter_input(INPUT_POST, 'ghpersonalaccesstoken'));
            $major_version = $this->inputMajorVersion();

            $output = array_merge($output, $this->Url->retrieveLatestVersion($personalToken, $major_version));
            $output['tagVersion'] = sanitize_text_field($output['tagVersion']);
            $output['downloadLink'] = esc_url_raw($output['downloadLink']);

            if (isset($output['rateLimitRemaining']) && $output['rateLimitRemaining'] <= 0) {
                $output['formResult'] = 'error';
                $output['formResultMessage'] = [
                    __('Your request rate limit on GItHub has been exceeded.', 'rd-fontawesome'),
                ];
            }

            unset($major_version, $personalToken);
            wp_send_json($output);
        }// retrieveLatestFAVersion


        /**
         * Save settings.
         */
        public function saveSettings()
        {
            // check permission.
            if (!current_user_can('manage_options')) {
                wp_die(__('You do not have permission to access this page.'));
                exit();
            }

            $output = [];
            check_ajax_referer('rdfontawesome_ajaxnonce', 'nonce');

            // prepare input for save in settings.
            $data = [];
            $data['ghpersonalaccesstoken'] = sanitize_text_field(filter_input(INPUT_POST, 'ghpersonalaccesstoken'));
            $data['major_version'] = $this->inputMajorVersion();
            $data['dequeue_css'] = sanitize_text_field(filter_input(INPUT_POST, 'dequeue_css'));
            $data['dequeue_js'] = sanitize_text_field(filter_input(INPUT_POST, 'dequeue_js'));
            $data['donot_enqueue'] = (isset($_POST['donot_enqueue']) && $_POST['donot_enqueue'] === '1' ? '1' : '0');

            // nromalize handles. (xx, yy to be xx,yy)
            $Strings = new \RdFontAwesome\App\Libraries\Strings();
            $data['dequeue_css'] = $Strings->normalizeHandlesString($data['dequeue_css']);
            $data['dequeue_js'] = $Strings->normalizeHandlesString($data['dequeue_js']);
            unset($Strings);

            $pendingScan = false;
            $didScanned = get_transient($this->scanDequeueDidScannedTransientName);
            $allPassed = true;
            if (
                (
                    !empty($data['dequeue_css']) || 
                    !empty($data['dequeue_js'])
                ) &&
                $didScanned !== 'true'
            ) {
                // if (dequeue CSS or JS has value) and did not scanned yet.
                // set task to scan.
                set_transient($this->scanDequeueTransientName, 'true', $this->scanDequeueTransientExpires);
                // set handle names to scan.
                set_transient($this->scanDequeueHandlesTransientName, json_encode([$data['dequeue_css'], $data['dequeue_js']]), $this->scanDequeueTransientExpires);
                // set custom nonce for use in front-end. WordPress don't have nonce functions supported in front end.
                $nonceValue = (string) mt_rand(111, 9999) . uniqid();
                set_transient('rd-fontawesome-nonce', $nonceValue, $this->scanDequeueTransientExpires);
                // tell user to open front pages to scan.
                $pendingScan = true;
                $output['pendingScan'] = $pendingScan;
                $output['formResult'] = 'warning';
                $output['formResultMessage'] = [
                    sprintf(
                        /* translators: %1$s open link, %2$s close link. */
                        __('Save is pending. Please open your %1$shome%2$s to let scaning process work for scan dequeue files and come back to save again.', 'rd-fontawesome'),
                        '<a href="' . add_query_arg(['nonce' => $nonceValue], home_url()) . '" target="home">',
                        '</a>'
                    ),
                ];
                unset($nonceValue);
            }

            if (isset($didScanned) && $didScanned === 'true') {
                // if already scanned. check if the left handles are matched.
                $handleNames = json_decode(get_transient($this->scanDequeueHandlesTransientName));
                $handleNamesResult = json_decode(get_transient($this->scanDequeueHandlesResultTransientName));
                $handleHashedResult = get_transient($this->scanDequeueHandlesHashedResultTransientName);
                $FAScan = new \RdFontAwesome\App\Libraries\FAScan();
                $formHashed = $FAScan->setHashNames($data['dequeue_css'], $data['dequeue_js']);
                if (
                    isset($handleNames[0]) && 
                    isset($handleNames[1]) && 
                    isset($handleNamesResult[0]) && 
                    isset($handleNamesResult[1]) && 
                    $handleNames[0] === $handleNamesResult[0] &&
                    $handleNames[1] === $handleNamesResult[1]
                ) {
                    // if all passed.
                    $allPassed = true;
                } else {
                    // if there are some failure.
                    $allPassed = false;
                    $output['formResult'] = 'warning';
                    $output['formResultMessage'] = [
                        __('Saved successfully.', 'rd-fontawesome'),
                        sprintf(
                            /* translators: %1$s the scanned result. */
                            __('Some of your handles were removed due to failed to verify it\'s Font Awesome. (%1$s)', 'rd-fontawesome'),
                            /* translators: %1$s the result of scanned CSS. */
                            sprintf(__('CSS: %1$s', 'rd-fontawesome'), '<code>' . ($handleNamesResult[0] ?? '')) . '</code>; ' .
                            /* translators: %1$s the result of scanned JS. */
                            sprintf(__('JS: %1$s', 'rd-fontawesome'), '<code>' . ($handleNamesResult[1] ?? '')) . '</code>'
                        ),
                    ];
                }
                $output['scanned'] = [
                    'handleNamesBeforeScan' => $handleNames,
                    'handleNamesResult' => $handleNamesResult,
                    'handleHashedResult' => $handleHashedResult,
                    'formHashed' => $formHashed,
                ];
                unset($FAScan, $formHashed, $handleNames);
                $pendingScan = false;

                // set output form value for re-assign to form controls.
                $output['form'] = [
                    'dequeue_css' => ($handleNamesResult[0] ?? ''),
                    'dequeue_js' => ($handleNamesResult[1] ?? ''),
                ];
                // re-assign scanned handles and add hash data.
                $data['dequeue_css'] = ($handleNamesResult[0] ?? '');
                $data['dequeue_js'] = ($handleNamesResult[1] ?? '');
                $data['dequeue_hashed'] = $handleHashedResult;// this is for check before use later in front end to prevent user manually enter in the json file.
                unset($handleHashedResult, $handleNamesResult);

                // delete all transient.
                delete_transient($this->scanDequeueDidScannedTransientName);
                delete_transient($this->scanDequeueHandlesHashedResultTransientName);
                delete_transient($this->scanDequeueHandlesResultTransientName);
                delete_transient($this->scanDequeueHandlesTransientName);
                delete_transient($this->scanDequeueTransientName);
            }
            unset($didScanned);

            if (isset($pendingScan) && $pendingScan === false) {
                // if no pending scan, save the data.
                $Settings = new \RdFontAwesome\App\Libraries\Settings();
                $Settings->saveSettings($data);
                unset($data, $Settings);

                if (isset($allPassed) && $allPassed === true) {
                    $output['formResult'] = 'success';
                    $output['formResultMessage'] = [
                        __('Saved successfully.', 'rd-fontawesome'),
                    ];
                }
            }
            unset($allPassed, $pendingScan);

            wp_send_json($output);
        }// saveSettings


        /**
         * Test GitHub personal access token.
         */
        public function testPersonalAccessToken()
        {
            // check permission.
            if (!current_user_can('manage_options')) {
                wp_die(__('You do not have permission to access this page.'));
                exit();
            }

            $output = [];
            check_ajax_referer('rdfontawesome_ajaxnonce', 'nonce');
            $personalToken = sanitize_text_field(filter_input(INPUT_POST, 'ghpersonalaccesstoken'));

            $output['testResult'] = $this->Url->testPersonalAccessToken($personalToken);
            if ($output['testResult']['success'] === true) {
                $output['formResult'] = 'success';
                $output['formResultMessage'] = [
                    __('Success!', 'rd-fontawesome'),
                ];
            } else {
                $output['formResult'] = 'error';
                $output['formResultMessage'] = [
                    __('Your access token is incorrect.', 'rd-fontawesome'),
                ];
            }

            unset($personalToken);
            wp_send_json($output);
        }// testPersonalAccessToken


        /**
         * Delete installed Font Awesome files.
         * 
         * @global \WP_Filesystem_Base $wp_filesystem
         */
        public function uninstallFA()
        {
            // check permission.
            if (!current_user_can('manage_options')) {
                wp_die(__('You do not have permission to access this page.'));
                exit();
            }

            $output = [];
            check_ajax_referer('rdfontawesome_ajaxnonce', 'nonce');

            global $wp_filesystem;
            WP_Filesystem();
            $result1 = $wp_filesystem->delete(plugin_dir_path(RDFONTAWESOME_FILE) . '.temparchive', true);
            $result2 = $wp_filesystem->delete(($this->getStaticPluginData())['targetPublishDir'], true);

            if (true === $result2) {
                $Settings = new \RdFontAwesome\App\Libraries\Settings();
                $allSettings = $Settings->getAllSettings();
                $allSettings['fontawesome_version'] = null;
                $Settings->saveSettings($allSettings);
                unset($allSettings, $Settings);
            }

            $output['result'] = (true === $result1 && true === $result2);
            unset($result1, $result2);

            if (true === $output['result']) {
                $output['formResult'] = 'success';
                $output['formResultMessage'] = [
                    __('Font Awesome files were removed successfully.', 'rd-fontawesome'),
                ];
            }

            wp_send_json($output);
        }// uninstallFA


    }// SettingsAjax
}
