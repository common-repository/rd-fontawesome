                <table class="rd-fontawesome-svinfo-table">
                    <tbody>
                        <tr>
                            <th><?php esc_html_e('WordPress', 'rd-fontawesome'); ?>:</th>
                            <td><?php esc_html_e($serverinfo['wpVersion']); ?></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e('PHP', 'rd-fontawesome'); ?>:</th>
                            <td><?php esc_html_e(PHP_VERSION); ?></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e('Execution timeout', 'rd-fontawesome'); ?>:</th>
                            <td><?php esc_html_e($serverinfo['phpExecTimeout']); ?></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e('Memory limit', 'rd-fontawesome'); ?>:</th>
                            <td><?php esc_html_e($serverinfo['phpMemoryLimit']); ?></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e('WordPress Memory limit', 'rd-fontawesome'); ?>:</th>
                            <td><?php esc_html_e($serverinfo['wpMemoryLimit']); ?>
                                <p class="description"><code>WP_MAX_MEMORY_LIMIT</code></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e('Plugin version', 'rd-fontawesome'); ?>:</th>
                            <td><?php esc_html_e(($serverinfo['pluginVersion'] ?? '?')); ?></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e('Writable directories and files', 'rd-fontawesome'); ?>:</th>
                            <td><?php 
                            if (isset($serverinfo['writable'])) {
                                foreach ($serverinfo['writable'] as $path => $pathResult) {
                                    echo '<p>' . esc_html($path) . '<br>';
                                    if ($pathResult === true) {
                                        esc_html_e('Yes.', 'rd-fontawesome');
                                    } elseif ($pathResult === false) {
                                        echo '<span class="rd-fontawesome-txt-error">' . __('No.', 'rd-fontawesome') . '</span>';
                                    } elseif ($pathResult === 'filenotexists') {
                                        echo '<span class="rd-fontawesome-txt-error">' . __('Not exists.', 'rd-fontawesome') . '</span>';
                                        echo ' ';
                                        _e('Maybe created automatically after first installed.', 'rd-fontawesome');
                                    }
                                    echo '</p>' . PHP_EOL;
                                }// endforeach;
                                unset($pathResult, $path);
                            }
                            ?></td>
                        </tr>
                    </tbody>
                </table><!--.rd-fontawesome-svinfo-table-->