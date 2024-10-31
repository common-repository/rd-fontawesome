<?php
/**
 * App trait.
 * 
 * @package rd-fontawesome
 * @license http://opensource.org/licenses/MIT MIT
 * @since 1.0.0
 */


namespace RdFontAwesome\App;


if (!trait_exists('\\RdFontAwesome\\App\\AppTrait')) {
    trait AppTrait
    {


            /**
             * @var \RdFontAwesome\App\Libraries\Loader
             */
            protected $Loader;


            /**
             * @var string Transient name of task that already did scanned. Value is 'true' if scanned.
             */
            protected $scanDequeueDidScannedTransientName = 'rd-fontawesome_scandequeue_didscanned';


            /**
             * @var string Transient name of scanned handle results and hashed already. 
             *      The result is JSON encoded of `md5` hash of the handle names. 
             *      First array is CSS handles, second array is JS handles.
             *      Example: ['xxxx', 'zzzz']; where `xxxx` and `zzzz` is the hashed names.
             *      See App/Libraries/FAScan:setHashNames() for more info.
             *      The value of this transient will be set after scanned.
             */
            protected $scanDequeueHandlesHashedResultTransientName = 'rd-fontawesome_scandequeue_handlehashedresult_transient';


            /**
             * @var string Transient name of the JSON encoded of scanned handle results that is no hashed.
             *      First array is CSS handles, second array is JS handles.
             *      Example: ['fontawesome-css1,fontawesome-for-plugin-css2', 'fontawesome-js1,fa-js-plugin-2'];
             */
            protected $scanDequeueHandlesResultTransientName = 'rd-fontawesome_scandequeue_handleresult_transient';


            /**
             * @var string Transient name of the JSON encoded of handle names. 
             *      First array is CSS handles, second array is JS handles.
             *      Example: ['fontawesome-css1,fontawesome-for-plugin-css2', 'fontawesome-js1,fa-js-plugin-2'];
             *      The value of this transient will not altered nor update after scanned.
             */
            protected $scanDequeueHandlesTransientName = 'rd-fontawesome_scandequeue_handles_transient';


            /**
             * @var int Transient for scan dequeue expiration in seconds.
             */
            protected $scanDequeueTransientExpires = (60*5);


            /**
             * @var string Transient name of task to scan target handles. Value is 'true'.
             */
            protected $scanDequeueTransientName = 'rd-fontawesome_scandequeue_transient';


            /**
             * Magic get
             * 
             * @param string $name
             */
            public function __get(string $name)
            {
                if (property_exists($this, $name)) {
                    return $this->{$name};
                }
                return null;
            }// __get


            /**
             * Get class instance from static method.
             * 
             * @return \self
             */
            public static function getInstance()
            {
                return new static();
            }// getInstance


            /**
             * Get static plugin data such as Font Awesome repository name, URL, plugin folder, plugin URL.
             * 
             * @return array Return associative array with keys:<br>
             *              'reponame' (string) Repository name. Example: name/repo<br>
             *              'githubURLs' (array) GitHub URLs.<br>
             *                      'latestAPIURL' (string) Latest release as API URL.<br>
             *                      'releasesAPIURL' (string) all releases.<br>
             *                      'tagsAPIURL' (string) all tags.<br>
             *                      'userProfile' (string) user profile.<br>
             *              'targetPublishDir' (string) The publish path for CSS and JS assets.<br>
             *              'targetPublishURLBase' (string) The publish URL for CSS and JS assets.<br>
             */
            public function getStaticPluginData(): array
            {
                $githubAPIURL = 'https://api.github.com';
                $reponame = 'FortAwesome/Font-Awesome';
                // @todo [rd-fontawesome] add major version for select box here.
                return [
                    'reponame' => $reponame,
                    'majorVersions' => [
                        4, 5, 6,
                    ],
                    'defaultMajorVersion' => 6,
                    'githubURLs' => [
                        'latestAPIURL' => $githubAPIURL . '/repos/' . $reponame . '/releases/latest',
                        'releasesAPIURL' => $githubAPIURL . '/repos/' . $reponame . '/releases',
                        'releastByTagAPIURL' => $githubAPIURL . '/repos/' . $reponame . '/releases/tags',
                        'tagsAPIURL' => $githubAPIURL . '/repos/' . $reponame . '/tags',
                        'userProfile' => $githubAPIURL . '/user',
                    ],
                    'targetPublishDir' => WP_CONTENT_DIR . '/uploads/rd-fontawesome',
                    'targetPublishURLBase' => WP_CONTENT_URL . '/uploads/rd-fontawesome',
                ];
            }// getStaticPluginData


    }
}
