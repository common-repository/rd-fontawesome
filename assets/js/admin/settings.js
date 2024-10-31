/* 
 * Admin Settings page.
 * 
 * @pakcage rd-fontawesome
 * @license http://opensource.org/licenses/MIT MIT
 * @since 1.0.0
 */


class RdFontAwesomeSettings {


    /**
     * Class constructor.
     * 
     * @returns {RdFontAwesomeSettings}
     */
    constructor() {
        this.ajaxLoading = false;
        this.formResultPlaceholder = document.getElementById('rd-fontawesome-form-result-placeholder');
    }// constructor


    /**
     * Detect current active tab target ID.
     * 
     * @private This method was called from `init()`.
     * @returns {null|string} Return `null` or current active tab target ID.
     */
    detectActiveTab() {
        let tabsNav = document.querySelectorAll('.rd-fontawesome-tab');
        let currentActiveTabId = null;

        if (tabsNav) {
            for (let i = 0; i < tabsNav.length; i++) {
                let item = tabsNav[i];
                if (item.classList.contains('active')) {
                    if (item.hash) {
                        currentActiveTabId = item.hash;
                        break;
                    }
                }
            }
        }

        return currentActiveTabId;
    }// detectActiveTab


    /**
     * Generate alert box.
     * 
     * @private
     * @param {mixed} messages The message as string or array.
     * @param {string} noticeType Accepted `success`, `error`. 
     * @returns {undefined}
     */
    generateAlertBox(messages, noticeType = 'success') {
        let alertBox = '<div class="notice notice-' + noticeType + ' is-dismissible">';
        if (typeof(messages) === 'string') {
            alertBox += '<p>' + messages + '</p>';
        } else {
            for (let i = 0; i < messages.length; i++) {
                alertBox += '<p>' + messages[i] + '</p>';
            }
        }
        alertBox += '<button class="notice-dismiss" type="button" onclick="return jQuery(this).parent().remove();"><span class="screen-reader-text">' + RdFontAwesomeSettingsObject.txtDismissNotice + '</span></button>';
        alertBox += '</div>';
        return alertBox;
    }// generateAlertBox


    /**
     * Initialize the class.
     * 
     * @returns {undefined}
     */
    init() {
        // tabs work.
        let activeTabId = this.detectActiveTab();
        this.setActiveTabContent(activeTabId);
        this.listenClickTab();

        this.testPersonalAccessToken();
        this.retrieveLatestVersion();
        this.installLatestVersion();
        this.uninstall();

        this.listenFormSubmit();
    }// init


    /**
     * Download and install latest version.
     * 
     * @private This method was called from `init()`.
     * @returns {undefined}
     */
    installLatestVersion() {
        let thisClass = this;
        let installedVersionElement = document.getElementById('rd-fontawesome-currentversion');
        let latestVersionElement = document.getElementById('rd-fontawesome-latestversion');
        let installBtn = document.getElementById('rd-fontawesome-install-latestversion-btn');

        if (installBtn) {
            installBtn.addEventListener('click', (e) => {
                e.preventDefault();

                let ajaxdata = {
                    'action': 'rdfontawesome_installlatestversion',
                    'nonce': RdFontAwesomeSettingsObject.nonce,
                    'ghpersonalaccesstoken': document.getElementById('rd-fontawesome-ghpersonalaccesstoken').value,
                    'major_version': document.getElementById('rd-fontawesome-major_version').value
                };

                if (false === this.ajaxLoading) {
                    this.ajaxLoading = true;
                    thisClass.resetPlaceholders();
                    installedVersionElement.innerText = RdFontAwesomeSettingsObject.txtLoading;

                    jQuery.ajax({
                        'url': ajaxurl,
                        'method': 'post',
                        'data': ajaxdata
                    })
                    .done((data, textStatus, jqXHR) => {
                        if (data) {
                            if (data.tagVersion) {
                                let previewHTML = thisClass.stripTags(data.tagVersion);
                                installedVersionElement.innerHTML = previewHTML;

                                let previewLatestVerHTML = '<a href="' + thisClass.stripTags(data.downloadLink) + '" target="_blank">' + thisClass.stripTags(data.tagVersion) + '</a>';
                                latestVersionElement.innerHTML = previewLatestVerHTML;
                            } else {
                                installedVersionElement.innerText = '-';
                            }

                            if (data.formResult && data.formResultMessage) {
                                let alertBox = thisClass.generateAlertBox(data.formResultMessage, data.formResult);
                                thisClass.formResultPlaceholder.innerHTML = alertBox;
                            }
                        }
                    })
                    .fail((jqXHR, textStatus, errorThrown) => {
                        let response;
                        if (jqXHR && jqXHR.responseJSON) {
                            response = jqXHR.responseJSON;
                        }

                        installedVersionElement.innerText = '-';

                        if (response && response.formResult && response.formResultMessage) {
                            let alertBox = thisClass.generateAlertBox(response.formResultMessage, response.formResult);
                            thisClass.formResultPlaceholder.innerHTML = alertBox;
                        }
                    })
                    .always((data, textStatus, jqXHR) => {
                        this.ajaxLoading = false;
                    });
                }
            });// end click event listener.
        }// endif install button
    }// installLatestVersion


    /**
     * Listen click on tab nav and set active tab content.
     * 
     * @private This method was called from `init()`.
     * @returns {undefined}
     */
    listenClickTab() {
        let thisClass = this;
        let tabsNav = document.querySelectorAll('.rd-fontawesome-tab');

        if (tabsNav) {
            tabsNav.forEach((item, index) => {
                item.addEventListener('click', (e) => {
                    e.preventDefault();
                    let thisElement = e.target;
                    if (thisElement.hash) {
                        thisClass.setActiveTabNav(thisElement);
                        thisClass.setActiveTabContent(thisElement.hash);
                    }
                });
            });
        }
    }// listenClickTab


    /**
     * Listen form submit and make AJAX save.
     * 
     * @private This method was called from `init()`.
     * @returns {undefined}
     */
    listenFormSubmit() {
        let thisClass = this;
        let thisForm = document.getElementById('rd-fontawesome-settings-form');
        let submitResultMessagePlaceholder = document.getElementById('rd-fontawesome-settings-submit-resultmessage');
        let useLoadingMessageInRsult = false;

        if (thisForm) {
            thisForm.addEventListener('submit', (e) => {
                e.preventDefault();

                let formData = new FormData(thisForm);
                formData.append('action', 'rdfontawesome_savesettings');
                formData.append('nonce', RdFontAwesomeSettingsObject.nonce);

                if (false === thisClass.ajaxLoading) {
                    thisClass.ajaxLoading = true;
                    thisClass.resetPlaceholders();
                    submitResultMessagePlaceholder.innerText = RdFontAwesomeSettingsObject.txtLoading;
                    useLoadingMessageInRsult = true;

                    jQuery.ajax({
                        'url': ajaxurl,
                        'method': 'post',
                        'data': new URLSearchParams(formData).toString()
                    })
                    .done((data, textStatus, jqXHR) => {
                        if (data) {
                            if (data.formResultMessage) {
                                let alertBox = thisClass.generateAlertBox(data.formResultMessage, data.formResult);
                                thisClass.formResultPlaceholder.innerHTML = alertBox;
                                if (data.pendingScan === true) {
                                    let submitResultMsg = '';
                                    for (let i = 0; i < data.formResultMessage.length; i++) {
                                        submitResultMsg += data.formResultMessage[i] + '<br>';
                                    }
                                    submitResultMessagePlaceholder.innerHTML = submitResultMsg;
                                    useLoadingMessageInRsult = false;
                                }
                            }

                            if (data.form && data.form.dequeue_css && data.form.dequeue_css) {
                                // if there are changed result of dequeue after scanned.
                                // update the value in form.
                                document.getElementById('rd-fontawesome-dequeue-css').value = data.form.dequeue_css
                                document.getElementById('rd-fontawesome-dequeue-js').value = data.form.dequeue_js
                            }
                        }
                    })
                    .fail((jqXHR, textStatus, errorThrown) => {
                        let response;
                        if (jqXHR && jqXHR.responseJSON) {
                            response = jqXHR.responseJSON;
                        }

                        if (response && response.formResultMessage) {
                            let alertBox = thisClass.generateAlertBox(response.formResultMessage, 'error');
                            thisClass.formResultPlaceholder.innerHTML = alertBox;
                        }
                    })
                    .always((data, textStatus, jqXHR) => {
                        thisClass.ajaxLoading = false;
                        if (useLoadingMessageInRsult === true) {
                            submitResultMessagePlaceholder.innerText = '';
                        }
                    });
                }
            });
        } else {
            console.error('Form is not exists.');
        }
    }// listenFormSubmit


    /**
     * Reset placeholders to empty.
     * 
     * @private
     * @returns {undefined}
     */
    resetPlaceholders() {
        this.formResultPlaceholder.innerHTML = '';
        document.getElementById('rd-fontawesome-test-ghpersonalaccesstoken-result').innerHTML = '';
        document.getElementById('rd-fontawesome-settings-submit-resultmessage').innerHTML = '';
    }// resetPlaceholders


    /**
     * Retrieve latest version.
     * 
     * @private This method was called from `init()`.
     * @returns {undefined}
     */
    retrieveLatestVersion() {
        let thisClass = this;
        let retrieveBtn = document.getElementById('rd-fontawesome-retrieve-latestversion-btn');
        let latestVersionElement = document.getElementById('rd-fontawesome-latestversion');

        if (retrieveBtn) {
            retrieveBtn.addEventListener('click', (e) => {
                e.preventDefault();

                let ajaxdata = {
                    'action': 'rdfontawesome_retrievelatestversion',
                    'nonce': RdFontAwesomeSettingsObject.nonce,
                    'ghpersonalaccesstoken': document.getElementById('rd-fontawesome-ghpersonalaccesstoken').value,
                    'major_version': document.getElementById('rd-fontawesome-major_version').value
                };

                if (false === thisClass.ajaxLoading) {
                    thisClass.ajaxLoading = true;
                    thisClass.resetPlaceholders();
                    latestVersionElement.innerText = RdFontAwesomeSettingsObject.txtLoading;

                    jQuery.ajax({
                        'url': ajaxurl,
                        'method': 'post',
                        'data': ajaxdata
                    })
                    .done((data, textStatus, jqXHR) => {
                        if (data) {
                            if (data.downloadLink && data.tagVersion) {
                                let previewHTML = '<a href="' + thisClass.stripTags(data.downloadLink) + '" target="_blank">' + thisClass.stripTags(data.tagVersion) + '</a>';
                                latestVersionElement.innerHTML = previewHTML;
                            } else {
                                latestVersionElement.innerText = '-';
                            }

                            if (data.formResult && data.formResultMessage) {
                                let alertBox = thisClass.generateAlertBox(data.formResultMessage, data.formResult);
                                this.formResultPlaceholder.innerHTML = alertBox;
                            }
                        }
                    })
                    .fail((jqXHR, textStatus, errorThrown) => {
                        latestVersionElement.innerText = '-';
                    })
                    .always((data, textStatus, jqXHR) => {
                        thisClass.ajaxLoading = false;
                    });
                }
            });// end click event listener.
        }// endif retrieve button
    }// retrieveLatestVersion


    /**
     * Set active to selected tab content.
     * 
     * @private This method was called from `init()`, `listenClickTab()`.
     * @param {string} selector
     * @returns {undefined}
     */
    setActiveTabContent(selector) {
        if (selector === null) {
            return ;
        }

        // remove all active tab content.
        document.querySelectorAll('.rd-fontawesome-tabs-content > *').forEach((item, index) => {
            item.classList.remove('active');
        });

        // set active tab content.
        let tabContent = document.querySelector(selector);
        if (tabContent) {
            tabContent.classList.add('active');
        }
    }// setActiveTabContent


    /**
     * Set active tab nav.
     * 
     * @private This method was called from `listenClickTab()`.
     * @param {object} HTMLElement
     * @returns {undefined}
     */
    setActiveTabNav(HTMLElement) {
        if (typeof(HTMLElement) === 'object') {
            // remove all active tab content.
            document.querySelectorAll('.rd-fontawesome-tab').forEach((item, index) => {
                item.classList.remove('active');
            });

            // set active tab nav.
            HTMLElement.classList.add('active');
        }
    }// setActiveTabNav


    /**
     * Strip HTML tags.
     * 
     * @param {string} string
     * @returns {unresolved}
     */
    stripTags(string) {
        return string.replace(/<\/?[^>]+(>|$)/g, "");
    }// stripTags


    /**
     * Test GitHub personal token.
     * 
     * @private This method was called from `init()`.
     * @returns {undefined}
     */
    testPersonalAccessToken() {
        let thisClass = this;
        let testTokenBtn = document.getElementById('rd-fontawesome-test-ghpersonalaccesstoken');
        let testResultPlaceholder = document.getElementById('rd-fontawesome-test-ghpersonalaccesstoken-result');

        testTokenBtn.addEventListener('click', (e) => {
            e.preventDefault();

            let ajaxdata = {
                'action': 'rdfontawesome_testghpersonalaccesstoken',
                'nonce': RdFontAwesomeSettingsObject.nonce,
                'ghpersonalaccesstoken': document.getElementById('rd-fontawesome-ghpersonalaccesstoken').value
            };

            if (false === thisClass.ajaxLoading) {
                thisClass.ajaxLoading = true;
                thisClass.resetPlaceholders();
                testResultPlaceholder.innerText = RdFontAwesomeSettingsObject.txtLoading;

                jQuery.ajax({
                    'url': ajaxurl,
                    'method': 'post',
                    'data': ajaxdata
                })
                .done((data, textStatus, jqXHR) => {
                    if (data && data.formResultMessage) {
                        testResultPlaceholder.innerText = data.formResultMessage;
                    }
                })
                .fail((jqXHR, textStatus, errorThrown) => {
                    testResultPlaceholder.innerText = '';
                })
                .always((data, textStatus, jqXHR) => {
                    thisClass.ajaxLoading = false;
                });
            }
        });

        // also prevent press enter on token input.
        let tokenInput = document.getElementById('rd-fontawesome-ghpersonalaccesstoken');
        tokenInput.addEventListener('keypress', (e) => {
            if (
                e.key === 'Enter'
            ) {
                e.preventDefault();
            }
        });
    }// testPersonalAccessToken


    /**
     * Uninstall currently downloaded and installed Font Awesome files.
     * 
     * @private This method was called from `init()`.
     * @returns {undefined}
     */
    uninstall() {
        let thisClass = this;
        let uninstallBtn = document.getElementById('rd-fontawesome-delete-installed-btn');
        let installedVersionElement = document.getElementById('rd-fontawesome-currentversion');

        uninstallBtn.addEventListener('click', (e) => {
            e.preventDefault();

            let ajaxdata = {
                'action': 'rdfontawesome_uninstallfontawesome',
                'nonce': RdFontAwesomeSettingsObject.nonce,
            };

            if (false === thisClass.ajaxLoading) {
                thisClass.ajaxLoading = true;
                thisClass.resetPlaceholders();

                jQuery.ajax({
                    'url': ajaxurl,
                    'method': 'post',
                    'data': ajaxdata
                })
                .done((data, textStatus, jqXHR) => {
                    if (data) {
                        if (data.result && data.result === true) {
                            installedVersionElement.innerText = '-';
                        }

                        if (data.formResult && data.formResultMessage) {
                            let alertBox = thisClass.generateAlertBox(data.formResultMessage, data.formResult);
                            this.formResultPlaceholder.innerHTML = alertBox;
                        }
                    }
                })
                .fail((jqXHR, textStatus, errorThrown) => {
                })
                .always((data, textStatus, jqXHR) => {
                    thisClass.ajaxLoading = false;
                });
            }
        });
    }// uninstall


}// RdFontAwesomeSettings


document.addEventListener('DOMContentLoaded', () => {
    let settings = new RdFontAwesomeSettings();
    
    settings.init();
});