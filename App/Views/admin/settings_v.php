<div id="rd-fontawesome-form-result-placeholder"></div>

<form id="rd-fontawesome-settings-form" method="post">
    <div class="rd-fontawesome-tabs-container">
        <div class="rd-fontawesome-tabs">
            <a class="rd-fontawesome-tab active" href="#rd-fontawesome-tab-settings"><?php esc_html_e('Settings'); ?></a>
            <a class="rd-fontawesome-tab" href="#rd-fontawesome-tab-svinfo"><?php esc_html_e('Server info', 'rd-fontawesome'); ?></a>
        </div><!--.rd-fontawesome-tabs-->
        <div class="rd-fontawesome-tabs-content">
            <div id="rd-fontawesome-tab-settings">
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row"><?php esc_html_e('Use major version', 'rd-fontawesome'); ?></th>
                            <td>
                                <select id="rd-fontawesome-major_version" name="major_version">
                                    <?php
                                    if (isset($allMajorVersions) && is_array($allMajorVersions)) {
                                        foreach ($allMajorVersions as $version) {
                                            echo '<option value="' . esc_attr($version) . '"';
                                            if (isset($settings['major_version']) && strval($settings['major_version']) === strval($version)) {
                                                echo ' selected';
                                            }
                                            echo '>' . esc_attr($version) . '</option>' . PHP_EOL;
                                        }// endforeach;
                                        unset($version);
                                    }
                                    unset($allMajorVersions);
                                    ?> 
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('GitHub personal access token', 'rd-fontawesome'); ?></th>
                            <td>
                                <input id="rd-fontawesome-ghpersonalaccesstoken" type="text" name="ghpersonalaccesstoken" value="<?php esc_attr_e(($settings['ghpersonalaccesstoken'] ?? '')); ?>" autocomplete="off" placeholder="user:token">
                                <button id="rd-fontawesome-test-ghpersonalaccesstoken" class="button" type="button"><?php esc_html_e('Test token', 'rd-fontawesome'); ?></button>
                                <span id="rd-fontawesome-test-ghpersonalaccesstoken-result"></span>
                                <p class="description"><?php 
                                    esc_html_e('GitHub personal access token is optional, but without it you can only access to GitHub only 60 requests per hour. Access with token can make up to 5,000 requests per hour.', 'rd-fontawesome');
                                    echo ' ';
                                    echo '<a href="https://docs.github.com/en/rest/guides/getting-started-with-the-rest-api#authentication" target="_blank">' . esc_html__('See reference.', 'rd-fontawesome') . '</a>';
                                ?></p>
                                <p class="description"><?php 
                                printf(
                                    /* translators: %1$s open link tag, %2$s close link tag */
                                    esc_html__('Open GitHub %1$ssettings page%2$s and go to Developer settings > Personal access tokens.', 'rd-fontawesome'),
                                    '<a href="https://github.com/settings/tokens" target="_blank">',
                                    '</a>'
                                );
                                echo ' ';
                                printf(
                                    /* translators: %1$s required scope for GitHub token. */
                                    esc_html__('Create token with these scopes: %1$s.', 'rd-fontawesome'),
                                    '<code>repo:status</code>, <code>repo_deployment</code>, <code>public_repo</code>'
                                );
                                ?></p>
                                <p class="description"><?php 
                                printf(
                                    /* translators: %1$s is user:token text, %2$s is user text. */
                                    esc_html__('The value must be %1$s where %2$s is GitHub username.', 'rd-fontawesome'),
                                    '<code>user:token</code>',
                                    '<code>user</code>'
                                );
                                ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Latest Font Awesome version', 'rd-fontawesome'); ?></th>
                            <td>
                                <p id="rd-fontawesome-latestversion">-</p>
                                <button id="rd-fontawesome-retrieve-latestversion-btn" class="button" type="button"><?php esc_html_e('Retrieve latest version info.', 'rd-fontawesome'); ?></button>
                                <p class="description"><?php esc_html_e('This will be use latest minor version of selected major version.', 'rd-fontawesome'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Installed Font Awesome version', 'rd-fontawesome'); ?></th>
                            <td>
                                <p id="rd-fontawesome-currentversion"><?php esc_html_e(($settings['fontawesome_version'] ?? '-')); ?></p>
                                <button id="rd-fontawesome-install-latestversion-btn" class="button" type="button"><?php esc_html_e('Install latest version', 'rd-fontawesome'); ?></button>
                                <button id="rd-fontawesome-delete-installed-btn" class="button button-danger" type="button"><?php esc_html_e('Uninstall Font Awesome', 'rd-fontawesome'); ?></button>
                                <p class="description"><?php esc_html_e('This will be use latest minor version of selected major version.', 'rd-fontawesome'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Unload enqueued CSS handles', 'rd-fontawesome'); ?></th>
                            <td>
                                <input id="rd-fontawesome-dequeue-css" class="regular-text" type="text" name="dequeue_css" value="<?php esc_attr_e(($settings['dequeue_css'] ?? '')); ?>">
                                <p class="description">
                                    <?php 
                                    esc_html_e('Dequeue the other Font Awesome CSS handles that was enqueued by other plugins or themes.', 'rd-fontawesome');
                                    echo ' ';
                                    esc_html_e('This affects only the front pages.', 'rd-fontawesome');
                                    echo ' ';
                                    /* translators: %1$s comma sign. */
                                    printf(__('Separate values by %1$s.', 'rd-fontawesome'), '<code>,</code>'); 
                                    ?> 
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Unload enqueued JS handles', 'rd-fontawesome'); ?></th>
                            <td>
                                <input id="rd-fontawesome-dequeue-js" class="regular-text" type="text" name="dequeue_js" value="<?php esc_attr_e(($settings['dequeue_js'] ?? '')); ?>">
                                <p class="description">
                                    <?php esc_html_e('Dequeue the  other Font Awesome JavaScript handles that was enqueued by other plugins or themes.', 'rd-fontawesome'); 
                                    echo ' ';
                                    esc_html_e('This affects only the front pages.', 'rd-fontawesome');
                                    echo ' ';
                                    /* translators: %1$s comma sign. */
                                    printf(__('Separate values by %1$s.', 'rd-fontawesome'), '<code>,</code>'); 
                                    ?> 
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="rd-fontawesome-dontenqueue"><?php esc_html_e('Do not enqueue assets', 'rd-fontawesome'); ?></label></th>
                            <td>
                                <input id="rd-fontawesome-dontenqueue" type="checkbox" name="donot_enqueue" value="1"<?php if (isset($settings['donot_enqueue']) && $settings['donot_enqueue'] === '1') {echo ' checked';} ?>>
                                <p class="description">
                                    <?php esc_html_e('Check this box to do not enqueue assets for this plugin.', 'rd-fontawesome'); ?> 
                                    <?php esc_html_e('The assets for this plugin such as CSS, fonts won\'t be loaded on the front pages.', 'rd-fontawesome'); ?> 
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table><!--.form-table-->
                <p class="submit">
                    <button id="rd-fontawesome-settings-submit" class="button button-primary" type="submit"><?php esc_html_e('Save Changes'); ?></button> 
                    <span id="rd-fontawesome-settings-submit-resultmessage"></span>
                </p>
            </div><!--#rd-fontawesome-tab-settings-->
            <div id="rd-fontawesome-tab-svinfo">
                <?php require __DIR__ . DIRECTORY_SEPARATOR . 'settings_serverinfo_tab.php'; ?> 
            </div><!--#rd-fontawesome-tab-svinfo-->
        </div><!--.rd-fontawesome-tabs-content-->
        
    </div><!--.rd-fontawesome-tabs-container-->
</form>