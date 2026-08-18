MailVotech.launchBuilder = function () {
    alert('Please enable the GrapesJS builder plugin (or another builder plugin) to use this feature.');
};

/**
 * Adds a hidded field which adds inBuilder=1 param to the request and will be returned in the response
 *
 * @param jQuery object of form
 */
MailVotech.inBuilderSubmissionOn = function(form) {
    const inBuilder = mQuery('<input type="hidden" name="inBuilder" value="1" />');
    const applyButton = mQuery('#btn-views-apply').css('pointer-events', 'none');
    form.append(inBuilder);
    form.one('submit:success', function (event, action, data) {
        if (data?.newContent) {
            // update the value of the optimistic lock version
            const selector = '#page_version, #emailform_version';
            const version = mQuery(data.newContent).find(selector).val();
            mQuery(this).find(selector).val(version);
        }
        applyButton.css('pointer-events', 'auto');
    });
}

/**
 * Removes the hidded field which adds inBuilder=1 param to the request
 *
 * @param jQuery object of form
 */
MailVotech.inBuilderSubmissionOff = function(form) {
    MailVotech.isInBuilder = false;
    mQuery('input[name="inBuilder"]').remove();
}

/**
 * Processes the Apply's button response
 *
 * @param  object response
 */
MailVotech.processBuilderErrors = function(response) {
    if (response.validationError) {
        mQuery('.btn-apply-builder').attr('disabled', true);
        mQuery('#builder-errors span').text(response.validationError);
        mQuery('#builder-errors').show('fast');
    }
};

/**
 * Opens Filemanager window
 */
MailVotech.openMediaManager = function() {
    MailVotech.openServerBrowser(
        mailvotechBasePath + '/elfinder',
        screen.width * 0.7,
        screen.height * 0.7
    );
}

/**
 * Removes stuff the Builder needs for it's magic but cannot be in the HTML result
 *
 * @param  object htmlContent
 */
MailVotech.sanitizeHtmlBeforeSave = function(htmlContent) {
    // Remove MailVotech's assets
    htmlContent.find('[data-source="mailvotech"]').remove();
    htmlContent.find('.atwho-container').remove();
    htmlContent.find('.fr-image-overlay, .fr-quick-insert, .fr-tooltip, .fr-toolbar, .fr-popup, .fr-image-resizer').remove();

    // Remove the slot focus highlight
    htmlContent.find('[data-slot-focus], [data-section-focus]').remove();

    // Replace all url("${URL}") with url('${URL}')
    var customHtml = MailVotech.domToString(htmlContent).replace(/url\(&quot;(.+)&quot;\)/g, 'url(\'$1\')');

    // Convert dynamic slot definitions into tokens
    // customHtml = MailVotech.convertDynamicContentSlotsToTokens(customHtml);

    // return MailVotech.prepareCodeModeBlocksBeforeSave(customHtml);
    return customHtml;
};

/**
 * Serializes DOM (full HTML document) to string
 *
 * @param  object dom
 * @return string
 */
MailVotech.domToString = function(dom) {
    if (typeof dom === 'string') {
        return dom;
    }
    var xs = new XMLSerializer();
    return xs.serializeToString(dom.get(0));
};

/**
 * Opens new window on the URL
 */
MailVotech.openServerBrowser = function(url, width, height) {
    var iLeft = (screen.width - width) / 2 ;
    var iTop = (screen.height - height) / 2 ;
    var sOptions = "toolbar=no,status=no,resizable=yes,dependent=yes" ;
    sOptions += ",width=" + width ;
    sOptions += ",height=" + height ;
    sOptions += ",left=" + iLeft ;
    sOptions += ",top=" + iTop ;
    var oWindow = window.open( url, "BrowseWindow", sOptions ) ;
}

/**
 * Initialize theme selection
 *
 * @param themeField
 */
MailVotech.initSelectTheme = function(themeField) {
    var customHtml = mQuery('textarea.builder-html');
    var isNew = MailVotech.isNewEntity('#page_sessionId, #emailform_sessionId');
    MailVotech.showChangeThemeWarning = true;
    MailVotech.builderTheme = themeField.val();

    if (isNew) {
        MailVotech.showChangeThemeWarning = false;

        // Populate default content
        if (!customHtml.length || !customHtml.val().length) {
            MailVotech.setThemeHtml(MailVotech.builderTheme);
        }
    }

    if (customHtml.length) {
        mQuery('[data-theme]').click(function(e) {
            e.preventDefault();
            var currentLink = mQuery(this);
            var theme = currentLink.attr('data-theme');
            var isCodeMode = (theme === 'mailvotech_code_mode');
            MailVotech.builderTheme = theme;

            if (MailVotech.showChangeThemeWarning && customHtml.val().length) {
                if (!isCodeMode) {
                    if (confirm(MailVotech.translate('mailvotech.core.builder.theme_change_warning'))) {
                        customHtml.val('');
                        MailVotech.showChangeThemeWarning = false;
                    } else {
                        return;
                    }
                } else {
                    if (confirm(MailVotech.translate('mailvotech.core.builder.code_mode_warning'))) {
                    } else {
                        return;
                    }
                }
            }

            // Set the theme field value
            themeField.val(theme);

            // Code Mode
            if (isCodeMode) {
                mQuery('.builder').addClass('code-mode');
                mQuery('.builder .code-editor').removeClass('hide');
                mQuery('.builder .code-mode-toolbar').removeClass('hide');
                mQuery('.builder .builder-toolbar').addClass('hide');
            } else {
                mQuery('.builder').removeClass('code-mode');
                mQuery('.builder .code-editor').addClass('hide');
                mQuery('.builder .code-mode-toolbar').addClass('hide');
                mQuery('.builder .builder-toolbar').removeClass('hide');

                // Load the theme HTML to the source textarea
                MailVotech.setThemeHtml(theme);
            }

            // Manipulate classes to achieve the theme selection illusion
            mQuery('.theme-list .panel').removeClass('theme-selected');
            currentLink.closest('.panel').addClass('theme-selected');
            mQuery('.theme-list .select-theme-selected').addClass('hide');
            mQuery('.theme-list .select-theme-link').removeClass('hide');
            currentLink.closest('.panel').find('.select-theme-selected').removeClass('hide');
            currentLink.addClass('hide');
        });
    }
};

/**
 * Set theme's HTML
 *
 * @param theme
 */
MailVotech.setThemeHtml = function(theme) {
    mQuery.get(mQuery('#builder_url').val()+'?template=' + theme, function(themeHtml) {
        var textarea = mQuery('textarea.builder-html');
        textarea.val(themeHtml);
    });
};

MailVotech.toggleBuilderButton = function (hide) {
    if (mQuery('.toolbar-form-buttons .toolbar-standard .btn-builder')) {
        if (hide) {
            // Move the builder button out of the group and hide it
            mQuery('.toolbar-form-buttons .toolbar-standard .btn-builder')
                .addClass('hide btn-standard-toolbar')
                .appendTo('.toolbar-form-buttons')

            mQuery('.toolbar-form-buttons .toolbar-dropdown i.ri-instance-fill').parent().addClass('hide');
        } else {
            if (!mQuery('.btn-standard-toolbar.btn-builder').length) {
                mQuery('.toolbar-form-buttons .toolbar-standard .btn-builder').addClass('btn-standard-toolbar')
            } else {
                // Move the builder button out of the group and hide it
                mQuery('.toolbar-form-buttons .btn-standard-toolbar.btn-builder')
                    .prependTo('.toolbar-form-buttons .toolbar-standard')
                    .removeClass('hide');

                mQuery('.toolbar-form-buttons .toolbar-dropdown i.ri-instance-fill').parent().removeClass('hide');
            }
        }
    }
};

MailVotech.removeAddVariantButton = function() {
    // Remove the Add Variant button for dynamicContent slots
    parent.mQuery('#customize-slot-panel').find('.panel-heading button').remove();
    MailVotech.reattachDEC();
};

MailVotech.reattachDEC = function() {
    if (typeof MailVotech.activeDEC !== 'undefined') {
        var element = MailVotech.activeDEC.detach();
        MailVotech.activeDECParent.append(element);
    }
};


MailVotech.isCodeMode = function() {
    return mQuery('a[data-theme=mailvotech_code_mode]').first().hasClass('hide');
};

window.document.fileManagerInsertImageCallback = function(selector, url) {
    if (MailVotech.isCodeMode()) {
        MailVotech.insertTextAtCMCursor(url);
    }
};

/**
 * @returns {string}
 */
MailVotech.getBuilderTokensMethod = function() {
    var method = 'page:getBuilderTokens';
    if (parent.mQuery('.builder').hasClass('email-builder')) {
        method = 'email:getBuilderTokens';
    }
    return method;
};
