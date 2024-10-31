<?php
/**
 * Font Awesome scanner class.
 * 
 * @package rd-fontawesome
 * @license http://opensource.org/licenses/MIT MIT
 * @since 1.0.0
 */


namespace RdFontAwesome\App\Libraries;


if (!class_exists('\\RdFontAwesome\\App\\Libraries\\FAScan')) {
    class FAScan
    {


        /**
         * @var array Search Font Awesome content keywords.
         *      Don't use too generic keywords such as `fa` (will be matched widefat), fal, etc.
         *      Must all lower case.
         */
        const SEARCH_FA_KEYWORDS = ['fa-', 'fontawesome', 'font awesome', 'font+awesome', 'font%20awesome', 'font-awesome', 'font_awesome'];


        /**
         * Do scan for Font Awesome assets content.
         * 
         * @param string $handles CSS or JS handles.
         * @param string $assetType The asset type. Accept css, js.
         * @return string Return scanned handles. The scanned but failed will be removed from handles string.
         */
        public function doScan(string $handles, string $assetType = 'css'): string
        {
            if ($handles === '' || is_null($handles)) {
                // if handles are empty.
                // return as-is and don't waste server resource for checking.
                return $handles;
            }


            $expHandles = explode(',', $handles);
            $expHandles = $this->makeHandlesUnique($expHandles);
            $registered = $this->getRegisteredHandles($assetType);

            foreach ($expHandles as $index => $handle) {
                $handle = trim($handle);

                if ($handle === 'rd-fontawesome') {
                    // if the handle is matched this plugin.
                    // remove it.
                    unset($expHandles[$index]);
                    continue;
                }

                // check for handle in registered handles in WordPress. -----
                $found = false;
                foreach ($registered as $item) {
                    if (
                        isset($item->handle) && 
                        $item->handle === $handle &&
                        isset($item->src)
                    ) {
                        // if found matched handle.
                        $assetFile = str_replace(trailingslashit(get_site_url()), ABSPATH, $item->src);
                        // check for correct file contents. -----
                        $assetContents = $this->getAssetContents($assetFile);

                        if (isset($assetContents)) {
                            if ($this->scanContents($assetContents) === true) {
                                // if found keywords in contents.
                                $found = true;
                                unset($assetContents, $assetFile);
                                break;
                            }
                            unset($assetContents);
                        }
                        // end check for correct file contents. -----

                        // in case that checked with the content but not found.
                        // check with the handle name. -----
                        if (in_array(strtolower($assetFile), static::SEARCH_FA_KEYWORDS)) {
                            // if found in handle name.
                            $found = true;
                            unset($assetFile);
                            break;
                        }
                        unset($assetFile);
                        // end check with the handle name. -----
                    }
                }// endforeach; registered handles in WordPress.
                unset($item);
                // end check for handle. -----

                if (false === $found) {
                    // if not found selected handle in registered handles.
                    // remove it.
                    unset($expHandles[$index]);
                }
            }// endforeach;
            unset($handle, $index, $registered);

            $handles = implode(',', $expHandles);
            unset($expHandles);

            return $handles;
        }// doScan


        /**
         * Get asset contents.
         * 
         * This method was called from `doScan()`.
         * 
         * @param string $assetFile
         * @return string|null Return string of contents or `null` if unable to get asset's content.
         */
        protected function getAssetContents(string $assetFile)
        {
            if (
                mb_substr($assetFile, 0, 2) === '//' ||
                stripos($assetFile, '://') !== false
            ) {
                // if asset file is full URL.
                $response = wp_remote_get($assetFile);
                $assetContents = wp_remote_retrieve_body($response);
                unset($response);
            } else {
                // if asset file is relative path or maybe full path.
                if (is_file(ABSPATH . $assetFile)) {
                    // if concat asset file with root full path and file exists, this means it is  relative path.
                    $assetContents = file_get_contents(ABSPATH . $assetFile);
                } elseif (is_file($assetFile)) {
                    // if the asset file itself exists, this means it is full path.
                    $assetContents = file_get_contents($assetFile);
                }
            }// endif check asset file path is URL or relative or full path.

            if (isset($assetContents)) {
                return $assetContents;
            }
            return null;
        }// getAssetContents


        /**
         * Get registered handles.
         * 
         * This method was called from `doScan()`.
         * 
         * @global \WP_Scripts $wp_scripts
         * @global \WP_Styles $wp_styles
         * @param string $assetType The asset type. Accept css, js.
         * @return array.
         */
        protected function getRegisteredHandles(string $assetType): array
        {
            global $wp_scripts, $wp_styles;

            if (strtolower($assetType) === 'css') {
                $registered = ($wp_styles->registered ?? []);
            } elseif (strtolower($assetType) === 'js') {
                $registered = ($wp_scripts->registered ?? []);
            } else {
                $registered = [];
            }

            if (!is_array($registered)) {
                $registered = [];
            }

            return $registered;
        }// getRegisteredHandles


        /**
         * Make array unique.
         * 
         * This method was called from `doScan()`.
         * 
         * @param array $handles
         * @return array
         */
        protected function makeHandlesUnique(array $handles): array
        {
            $handles = array_map('trim', $handles);
            $handles = array_unique($handles);
            return $handles;
        }// makeHandlesUnique


        /**
         * Scan contents for the keywords in class constant.
         * 
         * This method was called from `doScan()`.
         * 
         * @param string $contents
         * @return bool Return `true` if found one of keywords, `false` for otherwise.
         */
        protected function scanContents(string $contents): bool
        {
            $matches = [];
            $pattern = implode('|', array_map('preg_quote', static::SEARCH_FA_KEYWORDS));
            $result = preg_match('/' . $pattern . '/i', $contents, $matches);
            if ($result > 0) {
                return true;
            }
            return false;
        }// scanContents


        /**
         * Set hash value on names.
         * 
         * @param string $cssHandles The CSS handle names. Separate by comma (,).
         * @param string $jsHandles The JS handle names. Separate by comma (,).
         * @return array Return hashed names. The first array is hashed for CSS, the second array is hashed for JS.
         */
        public function setHashNames(string $cssHandles, string $jsHandles): array
        {
            return [
                md5($cssHandles),
                md5($jsHandles),
            ];
        }// setHashNames


    }// FAScan
}
