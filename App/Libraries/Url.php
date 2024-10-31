<?php
/**
 * URL class.
 * 
 * @package rd-fontawesome
 * @license http://opensource.org/licenses/MIT MIT
 * @since 1.0.0
 */


namespace RdFontAwesome\App\Libraries;


if (!class_exists('\\RdFontAwesome\\App\\Libraries\\Url')) {
    class Url
    {


        /**
         * @var array
         */
        protected $data;


        /**
         * @var array
         */
        public $downloadResult = [];


        /**
         * @var string
         */
        protected $tempDir;


        /**
         * @var \WP_Filesystem_Base
         */
        protected $WPFileSystem;


        /**
         * URL class constructor.
         * 
         * @param array $data The data that is in `AppTrait`.
         */
        public function __construct(array $data = [])
        {
            $this->data = $data;
        }// __construct


        /**
         * Magic get
         * 
         * @param string $name
         * @return mixed
         */
        public function __get(string $name)
        {
            if (property_exists($this, $name)) {
                return $this->{$name};
            }
            return null;
        }// __get


        /**
         * Build personal access token header that contain `Authorization` header name.
         * 
         * @param string $personalToken The personal access token.
         * @param array $args The argument for `wp_remote_xxx()`.
         * @return array Return array with ['headers']['Authorization'] keys in it.
         */
        protected function buildPersonalAccessTokenHeader(string $personalToken, array $args = []): array
        {
            if (!array_key_exists('headers', $args) || !is_array($args['headers'])) {
                $args['headers'] = [];
            }

            $args['headers']['Authorization'] = 'Basic ' . base64_encode($personalToken);

            return $args;
        }// buildPersonalAccessTokenHeader


        /**
         * Download file from target URL and extract/move to distribute folder with these steps.
         * 
         * 1. Download file from `$downloadLink`.
         * 2. Prepare temp folder for extract files.
         * 3. Extract zip files to temp folder.
         * 4. Check that downloaded file is valid Font Awesome file(s).
         * 5. Move valid files to publish folder (uploads/rd-fontawesome).
         * 
         * @global \WP_Filesystem_Base $wp_filesystem WordPress filesystem subclass.
         * @param string $downloadLink The download URL.
         * @param int $majorVersion The selected major version.
         * @return true|false|\WP_Error Return `true` on success, `false` on failure but be able to continue, `\WP_Error` on completely failure and need to be fixed.
         */
        public function downloadFile(string $downloadLink, int $majorVersion)
        {
            $tempDownloadedFile = download_url($downloadLink);
            if (is_wp_error($tempDownloadedFile)) {
                return $tempDownloadedFile;
            }

            // prepare new temp folder.
            $result = $this->prepareTempFolder();
            if (is_wp_error($result)) {
                return $result;
            }
            unset($result);

            // extract files to new temp folder.
            $result = $this->extractDownloadedFileToTempFolder($tempDownloadedFile);
            if (is_wp_error($result)) {
                return $result;
            }
            unset($result);

            // validate downloaded file.
            $result = $this->validateDownloadedFile($majorVersion);
            if (is_wp_error($result)) {
                return $result;
            }
            $newVersionDir = $result['newVersionDir'];
            unset($result);

            // move validated files to publish dir.
            $result = $this->moveToPublishDir($majorVersion, $newVersionDir);
            unset($newVersionDir);
            if (is_wp_error($result)) {
                return $result;
            }
            return $result;
        }// downloadFile


        /**
         * Extract downloaded file to temp folder.
         * 
         * This method was called from `downloadFile()`.<br>
         * This method must be called after `prepareTempFolder()`.
         * 
         * @param string $tempFilePath The temporary file that was downloaded.
         * @return \WP_Error|true Return `true` on success, `\WP_Error` on failure.
         */
        protected function extractDownloadedFileToTempFolder(string $tempFilePath)
        {
            $result = unzip_file($tempFilePath, $this->tempDir);
            $this->WPFileSystem->delete($tempFilePath);

            if (is_wp_error($result)) {
                return $result;
            }
            return true;
        }// extractDownloadedFileToTempFolder


        /**
         * Get move files and folders list.
         * 
         * @param int $majorVersion The selected major version.
         * @return null|array Return `array` of files list of matched major version, 
         *      return `null` if not found selected major version in the code.
         */
        protected function getMoveFilesList(int $majorVersion)
        {
            // @todo [rd-fontawesome] set major version move files here.
            if (4 === $majorVersion) {
                $moveFiles = [
                    'css',
                    'fonts',
                    'README.md',
                ];
            } elseif (5 === $majorVersion || 6 === $majorVersion) {
                $moveFiles = [
                    'css',
                    'js',
                    'sprites',
                    'svgs',
                    'webfonts',
                    'LICENSE.txt',
                ];
            } else {
                $moveFiles = null;
            }
            return $moveFiles;
        }// getMoveFilesList


        /**
         * Get required assets to enqueue.
         * 
         * @param int $majorVersion
         * @return array Return associative array with key:<br>
         *      `css` (array) The list of CSS files.<br>
         *      If not found any condition matched major version then return empty array.
         */
        public function getRequiredAssetsToEnqueue(int $majorVersion): array
        {
            $pluginUrlBase = $this->data['targetPublishURLBase'];

            // @todo [rd-fontawesome] set major version asset to enqueue here.
            if (4 === $majorVersion) {
                return [
                    'css' => [$pluginUrlBase . '/css/font-awesome.min.css'],
                ];
            } elseif (5 === $majorVersion || 6 === $majorVersion) {
                return [
                    'css' => [$pluginUrlBase . '/css/all.min.css'],
                ];
            }
            return [];
        }// getRequiredAssetsToEnqueue


        /**
         * Move validated downloaded files to publish path that can access from public.
         * 
         * Also validate again that files were moved successfully.
         * 
         * This method was called from `downloadFile()`.<br>
         * This method must be called after `validateDownlaodedFile()`.
         * 
         * @param int $majorVersion The selected major version.
         * @param string $newVersionDir The folder name after extracted zip files before go into real assets files.
         * @return \WP_Error|bool Return `true` on success, `false` on soft failure but can continue, `\WP_Error` on failure.
         */
        protected function moveToPublishDir(int $majorVersion, string $newVersionDir)
        {
            $targetPublishDir = $this->data['targetPublishDir'];

            // prepare distributed folder
            $this->WPFileSystem->delete($targetPublishDir, true);
            $result = wp_mkdir_p($targetPublishDir);

            if (false === $result) {
                return new \WP_Error(
                    'RDFA_CANTCREATEDIR', 
                    /* translators: %1$s the public path to Font Awesome asset. */
                    sprintf(__('Unable to create directory at %1$s.', 'rd-fontawesome'), $targetPublishDir)
                );
            }
            unset($result);

            // prepare folders and files to move.
            $moveFiles = $this->getMoveFilesList($majorVersion);
            if (!is_array($moveFiles)) {
                return new \WP_Error(
                    'RDFA_UNKNOW_SELECTED_MAJORVERSION',
                    __('Unknown selected major version.', 'rd-fontawesome'),
                    $majorVersion
                );
            }

            // start move.
            $results = [];
            $movedSuccess = [];
            foreach ($moveFiles as $moveFile) {
                $result = rename($this->tempDir . '/' . $newVersionDir . '/' . $moveFile, $targetPublishDir . '/' . $moveFile);
                $results[$this->tempDir . '/' . $newVersionDir . '/' . $moveFile] = var_export($result, true);
                if (true === $result) {
                    $movedSuccess[] = $moveFile;
                }
            }// endforeach;
            unset($moveFile, $result);

            $this->downloadResult['move'] = [
                'results' => $results,
                'movedSuccess' => $movedSuccess,
            ];
            unset($moveFiles, $movedSuccess, $results);
            unset($targetPublishDir);

            // validate moved file that all are existed.
            if (true === $this->validateMovedFiles($majorVersion)) {
                $return = true;
                // delete temporary folder in the plugin.
                $this->WPFileSystem->delete($this->tempDir, true);
            } else {
                $return = false;
            }

            return $return;
        }// moveToPublishDir


        /**
         * Prepare temporary folder inside the plugin.
         * 
         * This method was called from `downloadFile()`.<br>
         * This method will be set `tempDir`, `WPFileSystem` properties for later usage.
         * 
         * @global \WP_Filesystem_Base $wp_filesystem
         * @return \WP_Error|true Return `true` on success, `\WP_Error` on failure.
         */
        protected function prepareTempFolder()
        {
            $this->tempDir = plugin_dir_path(RDFONTAWESOME_FILE) . '.temparchive';

            global $wp_filesystem;
            WP_Filesystem();

            $this->WPFileSystem = $wp_filesystem;
            $this->WPFileSystem->delete($this->tempDir, true);
            $result = wp_mkdir_p($this->tempDir);

            if (false === $result) {
                return new \WP_Error(
                    'RDFA_CANTCREATEDIR', 
                    /* translators: %1$s the plugin directory path. */
                    sprintf(__('Unable to create directory at %1$s.', 'rd-fontawesome'), plugin_dir_path(RDFONTAWESOME_FILE))
                );
            }
            unset($result);
            return true;
        }// prepareTempFolder


        /**
         * Retrieve latest version based on selected major version..
         * 
         * @param string $personalToken Personal access token.
         * @param int $majorVersion The selected major version.
         * @return array Return associative array.<br>
         *      `rateLimitRemaining` (int) requests rate limit remaining.<br>
         *      `rateLimitUsed` (int) requests rate limit used.<br>
         *      `matchedMinorVersions` (array) All minor versions under the selected major version.<br>
         *      `listTagsIterationCount` (int) Number of API request pages loop while get all tags.<br>
         *      `tagVersion` (string) Tag version (if avaialble).<br>
         *      `downloadLink` (string) Download link (if available).<br>
         *      `isZipball` (bool) If `true` means it is using GitHub zipball auto URL.<br>
         */
        public function retrieveLatestVersion(string $personalToken, int $majorVersion): array
        {
            $output = [];
            $url = $this->data['githubURLs']['tagsAPIURL'] . '?per_page=100';
            $args = [];
            if (!empty($personalToken)) {
                $args = $this->buildPersonalAccessTokenHeader($personalToken, $args);
            }

            $endLoop = false;
            $maxOriginalTag = '0';
            $maxTag = '0';
            $maxTagZipUrl = '';
            $matchedMinorVersions = [];
            $iterationCount = 0;

            do {
                $iterationCount++;
                $response = wp_remote_get($url . '&page=' . $iterationCount, $args);
                $responseCode = (int) wp_remote_retrieve_response_code($response);
                $headerLink = wp_remote_retrieve_header($response, 'link');
                $output['rateLimitRemaining'] = (int) wp_remote_retrieve_header($response, 'x-ratelimit-remaining');
                $output['rateLimitUsed'] = (int) wp_remote_retrieve_header($response, 'x-ratelimit-used');
                $body = json_decode(wp_remote_retrieve_body($response));
                unset($response);

                if (is_array($body) && $responseCode >= 200 && $responseCode < 300) {
                    // if requested success.
                    foreach ($body as $tag) {
                        $tagVersion = ($tag->name ?? '');
                        $tagVersion = preg_replace('/^([a-z]+)(\d+)/mi', '$2', $tagVersion);// remove alphabet before number out. EX: v1.2.3 will be 1.2.3

                        if (
                            '' !== $tagVersion &&
                            version_compare($tagVersion, (string) $majorVersion, '>=') &&
                            version_compare($tagVersion, (string) ($majorVersion + 1), '<')
                        ) {
                            // if this tag is >= selected major version and < next major version.
                            // we found matched major version.
                            $matchedMinorVersions[] = $tagVersion;
                            if (version_compare($tagVersion, $maxTag, '>=')) {
                                // if this found major version is >= (newer) than current max tag.
                                // set it.
                                $maxOriginalTag = $tag->name;
                                $maxTag = $tagVersion;
                                $maxTagZipUrl = ($tag->zipball_url ?? '');
                            }
                        }
                    }// endforeach;
                    unset($tag, $tagVersion);

                    if (stripos($headerLink, '"next"') !== false) {
                        // if there is next page.
                        if ($iterationCount > 1000) {
                            // if loop too much
                            $endLoop = true;
                        }
                    } else {
                        // if there is no next page.
                        $endLoop = true;
                    }
                } else {
                    // if request failed.
                    $endLoop = true;
                }// endif body is array.

            } while ($endLoop === false);
            unset($args, $body, $endLoop, $headerLink, $responseCode, $url);

            $output['tagVersion'] = $maxTag;
            $output['downloadLink'] = $maxTagZipUrl;
            $output['isZipball'] = !empty($maxTagZipUrl);
            $output['listTagsIterationCount'] = $iterationCount;
            $output['matchedMinorVersions'] = $matchedMinorVersions;
            unset($iterationCount, $matchedMinorVersions, $maxTag, $maxTagZipUrl);

            if ($majorVersion > 4 && $maxOriginalTag !== '0') {
                // if major version > 4 and at least found a tag.
                // get release data to retrieve manual uploaded asset.
                $url = $this->data['githubURLs']['releastByTagAPIURL'] . '/' . $maxOriginalTag;
                $args = [];
                if (!empty($personalToken)) {
                    $args = $this->buildPersonalAccessTokenHeader($personalToken, $args);
                }

                $response = wp_remote_get($url, $args);
                $responseCode = (int) wp_remote_retrieve_response_code($response);
                $body = json_decode(wp_remote_retrieve_body($response));
                unset($response);

                if (is_object($body) && $responseCode >= 200 && $responseCode < 300) {
                    // if requested success.
                    if (isset($body->assets) && is_array($body->assets)) {
                        foreach ($body->assets as $asset) {
                            if (isset($asset->browser_download_url) && stripos($asset->browser_download_url, 'web') !== false) {
                                $downloadLink = $asset->browser_download_url;
                                break;
                            }
                        }// endforeach;
                        unset($asset);
                    }// endif there are assets
                }
                unset($args, $body, $responseCode, $url);

                if (isset($downloadLink) && is_string($downloadLink)) {
                    $output['downloadLink'] = $downloadLink;
                    $output['isZipball'] = false;
                }
            }// endif major version may have release with manual uploaded asset.

            unset($maxOriginalTag);
            return $output;
        }// retrieveLatestVersion


        /**
         * Test GitHub personal access token.
         * 
         * @param string $personalToken Personal access token.
         * @return array Return array with these array keys:<br>
         *      `responseCode` (int) HTTP response code.<br>
         *      `rateLimitRemaining` (int) requests rate limit remaining.<br>
         *      `rateLimitUsed` (int) requests rate limit used.<br>
         *      `success` (bool) Test status.<br>
         */
        public function testPersonalAccessToken(string $personalToken): array
        {
            $output = [];

            $url = $this->data['githubURLs']['userProfile'];
            $args = $this->buildPersonalAccessTokenHeader($personalToken);

            $response = wp_remote_get($url, $args);
            unset($args, $url);
            $output['responseCode'] = (int) wp_remote_retrieve_response_code($response);
            $output['rateLimitRemaining'] = (int) wp_remote_retrieve_header($response, 'x-ratelimit-remaining');
            $output['rateLimitUsed'] = (int) wp_remote_retrieve_header($response, 'x-ratelimit-used');

            if ($output['responseCode'] >= 200 && $output['responseCode'] < 300) {
                $output['success'] = true;
            } else {
                $output['success'] = false;
            }

            return $output;
        }// testPersonalAccessToken


        /**
         * Validated downloaded file.
         * 
         * This method was called from `downloadFile()`.<br>
         * This method must be called after `extractDownlaodedFileToTempFolder()`.
         * 
         * @param int $majorVersion The selected major version.
         * @return \WP_Error|array Return array with this key on success:<br>
         *      `newVersionDir` (string) The folder name after extracted zip files before go into real assets files.<br>
         *      Return `\WP_Error` on failure.
         */
        protected function validateDownloadedFile(int $majorVersion)
        {
            $filesInNewtemp = $this->WPFileSystem->dirlist($this->tempDir);
            $validFontAwesomeFiles = false;
            $newVersionDir = null;

            // @todo [rd-fontawesome] set major version validate files here.
            if (4 === $majorVersion) {
                $validFiles = [
                    'css/font-awesome.min.css',
                    'fonts',
                ];
            } elseif (5 === $majorVersion || 6 === $majorVersion) {
                $validFiles = [
                    'css/all.min.css',
                    'js/all.min.js',
                    'sprites',
                    'webfonts',
                ];
            } else {
                return new \WP_Error(
                    'RDFA_UNKNOW_SELECTED_MAJORVERSION',
                    __('Unknown selected major version.', 'rd-fontawesome'),
                    $majorVersion
                );
            }

            if (is_array($filesInNewtemp)) {
                $newVersionDir = array_key_first($filesInNewtemp);// the extracted will be FontAwesome-x.x.x/css This will be get FontAwesome-x.x.x folder name.
            }
            unset($filesInNewtemp);

            foreach ($validFiles as $validFile) {
                if (file_exists($this->tempDir . DIRECTORY_SEPARATOR . $newVersionDir . DIRECTORY_SEPARATOR . $validFile)) {
                    $validFontAwesomeFiles = true;
                } else {
                    $this->WPFileSystem->delete($this->tempDir, true);

                    unset($newVersionDir, $validFile, $validFiles, $validFontAwesomeFiles);
                    return new \WP_Error(
                        'RDFA_REQUIRED_FILE_NOTFOUND',
                        sprintf(
                            /* translators: %1$s the required file name. */
                            __('The required Font Awesome file is not exists. (%1$s)', 'rd-fontawesome'), 
                            '<code>' . $validFile . '</code>'
                        ),
                        $validFile
                    );
                }
            }// endforeach;
            unset($validFile, $validFiles);

            if (isset($validFontAwesomeFiles) && true === $validFontAwesomeFiles) {
                return [
                    'newVersionDir' => $newVersionDir,
                ];
            }

            // i don't know how possible that it can go to this line. :-/ leave it just in case.
            return new \WP_Error(
                'RDFA_INVALID_FAFILES',
                __('Invalid Font Awesome files.', 'rd-fontawesome')
            );
        }// validateDownloadedFile


        /**
         * Validate moved files.
         * 
         * This method was called from `moveToPublishDir()`.
         * 
         * This method will trigger error if there is some failure, please see the log for more details.
         * 
         * @param int $majorVersion The selected major version.
         * @return bool Return `true` on success, `false` on failure.
         */
        protected function validateMovedFiles(int $majorVersion)
        {
            $targetPublishDir = $this->data['targetPublishDir'];
            $moveFiles = $this->getMoveFilesList($majorVersion);

            sleep(1);// prevent some time errors file access denied. can't find the way to fix it.
            $validatedMoved = [];
            $results = [];
            foreach ($moveFiles as $file) {
                if (file_exists($targetPublishDir . '/' . $file)) {
                    $validatedMoved[] = $file;
                } else {
                    $results[] = $targetPublishDir . DIRECTORY_SEPARATOR . $file;
                }
            }// endforeach;
            unset($file);

            if (count($moveFiles) === count($validatedMoved)) {
                // if all files and folders were moved successfully.
                $return = true;
                $this->downloadResult['move']['validatedAllSuccess'] = true;
            } else {
                // if some files not moved.
                $return = false;
                $this->downloadResult['move']['validatedAllSuccess'] = false;
                $this->downloadResult['move']['failedValidatedMove'] = $results;

                // it's PHP error, show in the log, no need to translate.
                trigger_error(sprintf('Unable to move some files (%s)', var_export($results, true)), E_USER_WARNING);
            }

            unset($moveFiles, $results, $validatedMoved);
            return $return;
        }// validateMovedFiles


    }
}
