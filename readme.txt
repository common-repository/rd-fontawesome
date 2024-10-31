=== Rundiz Font Awesome ===
Contributors: okvee
Tags: fontawesome, font awesome, icons
Requires at least: 5.0
Tested up to: 6.6
Stable tag: 1.0.3
Requires PHP: 7.0
License: MIT
License URI: http://opensource.org/licenses/MIT
 
Use Font Awesome from your host and update from GitHub.

== Description ==
Use Font Awesome icons from your own host/server. Update the latest releases from GitHub.

Choose the major version and install the latest minor version. For example: Choose major version 6, and install the latest of 6.x.xx.  
Currently supported for version 4, 5, 6.

This plugin did not use CDN (content delivery network). If you want to use CDN, please use official Font Awesome plugin instead.

You can dequeue duplicated Font Awesome handles from other plugins or themes to prevent conflict.

You can disable enqueue from this plugin in case you would like to do it manually.  
To get asset files for manually enqueue, use this code.
<pre>
if (function_exists('rdfontawesome_get_enqueue_files')) {
    $result = rdfontawesome_get_enqueue_files();
    if (isset($result['css']) && is_array($result['css'])) {
        $faVersion = ($result['faVersion'] ?? false);
        foreach ($result['css'] as $css) {
            wp_enqueue_style('myplugin_fontawesome', $css, [], $faVersion);
        }
        unset($css, $faVersion);
    }
}
</pre>

To get URL base of the Font Awesome assets for use another files, use this code.
<pre>
if (function_exists('rdfontawesome_get_public_url_base')) {
    echo rdfontawesome_get_public_url_base();
}
</pre>

Font Awesome files are belong to https://fontawesome.com/

== Frequently Asked Questions ==

= Does this support Font Awesome Pro? =
No, if you would like to use Pro, please use official Font Awesome plugin. The Font Awesome Pro couldn't be download via GitHub.

= Can I use it on my web hosting or server? =
Yes, this plugin will not use CDN. So, you can use it on your own server or hosting. This is good for some restriction.

= Can I use with my theme or other plugins? =
Yes, you can use with any theme or plugins. There are options to dequeue the CSS & JS that was enqueued by other themes or plugins on the front pages. Just dequeue them to prevent duplicate (front pages only).

= Does it supported short code? =
No, this plugin does not supported any short code.

= What kind of assets this plugin use? =
This plugin use CSS and web fonts. It doesn't use any JavaScript or SVG files.

= What asset files this plugin will be download and installed? =
This plugin download all necessary files that is ready to use such as CSS, fonts or including SVG for newer version of Font Awesome. The pre-processing such as Less, Sass files will not be use.

== Screenshots ==
1. Settings page.

== Changelog ==
= 1.0.3 =
2024-07-07

* Fix check latest version on install command.

= 1.0.2 =
2022-02-18

* Add Font Awesome version 6 supported.

= 1.0.1 =
2022-01-22

* Fix check for downloaded zip file is not working on Linux.

= 1.0.0 =
2022-01-22

* Initial release.