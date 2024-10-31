<?php
/** 
 * Rundiz Font Awesome functions.
 * 
 * @package rd-fontawesome
 * @license http://opensource.org/licenses/MIT MIT
 * @since 1.0.0
 */


if (!function_exists('rdfontawesome_get_enqueue_files')) {
    /**
     * Get Font Awesome asset files that will be use for enqueue manually.
     * 
     * This file is copied from `App\Controllers\Front\Hooks\EnqueueDequeue::enqueueAssets()`.
     * 
     * @return array Return associative array. Example: <pre>
     *      'css' => ['file/to/use.css'],
     *      'faVersion' => '5.15.4',
     * </pre>
     *      But if it failed or not installed, this function will return empty array.
     */
    function rdfontawesome_get_enqueue_files(): array
    {
        $Settings = new \RdFontAwesome\App\Libraries\Settings();
        $allSettings = $Settings->getAllSettings();
        unset($Settings);
        $App = new \RdFontAwesome\App\App();

        $faVersion = ($allSettings['fontawesome_version'] ?? false);
        if (false === $faVersion) {
            // if not yet installed.
            return [];
        }

        $majorVersion = ($allSettings['major_version'] ?? ($App->getStaticPluginData())['defaultMajorVersion']);
        $Url = new \RdFontAwesome\App\Libraries\Url($App->getStaticPluginData());
        $asset = $Url->getRequiredAssetsToEnqueue($majorVersion);
        unset($App, $majorVersion, $Url);

        if (!empty($asset) && is_array($asset)) {
            $output = $asset;
            $output['faVersion'] = $faVersion;
        } else {
            $output = [];
        }

        unset($asset, $faVersion);
        return $output;
    }// rdfontawesome_get_enqueue_files
}


if (!function_exists('rdfontawesome_get_public_url_base')) {
    /**
     * Get plugin's public URL base where the Font Awesome files will be installed into.
     * 
     * @return string Return the full URL to directory base that Font Awesome will be installed into. No trailing slash.
     */
    function rdfontawesome_get_public_url_base(): string
    {
        $App = new \RdFontAwesome\App\App();
        return ($App->getStaticPluginData())['targetPublishURLBase'];
    }// rdfontawesome_get_public_url_base
}