//PageBundle
MailVotech.pageOnLoad = function (container, response) {
    if (mQuery(container + ' #list-search').length) {
        MailVotech.activateSearchAutocomplete('list-search', 'page.page');
    }

    if (mQuery(container + ' #page_template').length) {
        MailVotech.toggleBuilderButton(mQuery('#page_template').val() == '');

        // Preload tokens for code mode builder
        MailVotech.getTokens(MailVotech.getBuilderTokensMethod(), function(){});
        MailVotech.initSelectTheme(mQuery('#page_template'));
    }

    // Open the builder directly when saved from the builder
    if (response && response.inBuilder) {
        MailVotech.launchBuilder('page');
        MailVotech.processBuilderErrors(response);
    }
};

MailVotech.getPageAbTestWinnerForm = function(abKey) {
    if (abKey && mQuery(abKey).val() && mQuery(abKey).closest('.form-group').hasClass('has-error')) {
        mQuery(abKey).closest('.form-group').removeClass('has-error');
        if (mQuery(abKey).next().hasClass('help-block')) {
            mQuery(abKey).next().remove();
        }
    }

    MailVotech.activateLabelLoadingIndicator('page_variantSettings_winnerCriteria');

    var pageId = mQuery('#page_sessionId').val();
    var query  = "action=page:getAbTestForm&abKey=" + mQuery(abKey).val() + "&pageId=" + pageId;

    mQuery.ajax({
        url: mailvotechAjaxUrl,
        type: "POST",
        data: query,
        dataType: "json",
        success: function (response) {
            if (typeof response.html != 'undefined') {
                if (mQuery('#page_variantSettings_properties').length) {
                    mQuery('#page_variantSettings_properties').replaceWith(response.html);
                } else {
                    mQuery('#page_variantSettings').append(response.html);
                }

                if (response.html != '') {
                    MailVotech.onPageLoad('#page_variantSettings_properties', response);
                }
            }

            MailVotech.removeLabelLoadingIndicator();

        },
        error: function (request, textStatus, errorThrown) {
            MailVotech.processAjaxError(request, textStatus, errorThrown);
            spinner.remove();
        },
        complete: function () {
            MailVotech.removeLabelLoadingIndicator();
        }
    });
};
