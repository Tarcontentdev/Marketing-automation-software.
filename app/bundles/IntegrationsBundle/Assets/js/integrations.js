MailVotech.integrationsConfigOnLoad = function () {
    mQuery('.integration-keyword-filter').each(function() {
        mQuery(this).off("keyup.integration-filter").on("keyup.integration-filter", function (event) {
            var integration = mQuery(this).attr('data-integration');
            var object = mQuery(this).attr('data-object');
            MailVotech.getPaginatedIntegrationFields(
                {
                    'integration': integration,
                    'object': object,
                    'keyword': mQuery(this).val()
                },
                1,
                this
            );
        });
    });

    MailVotech.activateIntegrationFieldUpdateActions();
};

MailVotech.getPaginatedIntegrationFields = function(settings, page, element) {
    var requestName = settings.integration + '-' + settings.object;
    var action = mailvotechBaseUrl + 's/integration/' + settings.integration + '/config/' + settings.object + '/' + page;
    if (settings.keyword) {
        action = action + '?keyword=' + settings.keyword;
    }

    if (typeof MailVotech.activeActions == 'undefined') {
        MailVotech.activeActions = {};
    } else if (typeof MailVotech.activeActions[requestName] != 'undefined') {
        MailVotech.activeActions[requestName].abort();
    }

    var object    = settings.object;
    var fieldsTab = '#field-mappings-'+object+'-container';

    if (element && mQuery(element).is('input')) {
        MailVotech.activateLabelLoadingIndicator(mQuery(element).attr('id'));
    }
    var fieldsContainer = '#field-mappings-'+object;

    var modalId = '#'+mQuery(fieldsContainer).closest('.modal').attr('id');
    MailVotech.startModalLoadingBar(modalId);

    MailVotech.activeActions[requestName] = mQuery.ajax({
        showLoadingBar: false,
        url: action,
        type: "POST",
        dataType: "json",
        success: function (response) {
            if (response.success) {
                mQuery(fieldsContainer).html(response.html);
                MailVotech.onPageLoad(fieldsContainer);
                MailVotech.activateIntegrationFieldUpdateActions();
                if (mQuery(fieldsTab).length) {
                    mQuery(fieldsTab).removeClass('hide');
                }
            } else if (mQuery(fieldsTab).length) {
                mQuery(fieldsTab).addClass('hide');
            }

            if (element) {
                MailVotech.removeLabelLoadingIndicator();
            }

            MailVotech.stopModalLoadingBar(modalId);
        },
        error: function (request, textStatus, errorThrown) {
            MailVotech.processAjaxError(request, textStatus, errorThrown);
        },
        complete: function () {
            delete MailVotech.activeActions[requestName]
        }
    });
};

MailVotech.updateIntegrationField = function(integration, object, field, fieldOption, fieldValue) {
    var action = mailvotechBaseUrl + 's/integration/' + integration + '/config/' + object + '/field/' + field;
    var modal = mQuery('form[name=integration_config]').closest('.modal');
    var requestName = integration + object + field + fieldOption;

    // Disable submit buttons until the action is done so nothing is lost
    mQuery(modal).find('.modal-form-buttons .btn').prop('disabled', true);

    if (typeof MailVotech.activeActions == 'undefined') {
        MailVotech.activeActions = {};
    } else if (typeof MailVotech.activeActions[requestName] != 'undefined') {
        MailVotech.activeActions[requestName].abort();
    }

    MailVotech.startModalLoadingBar(mQuery(modal).attr('id'));

    // Must use bracket notation to use variable for key
    var obj = {};
    obj[fieldOption] = fieldValue;

    MailVotech.activeActions[requestName] = mQuery.ajax({
        showLoadingBar: false,
        url: action,
        type: "POST",
        dataType: "json",
        data: obj,
        error: function (request, textStatus, errorThrown) {
            MailVotech.processAjaxError(request, textStatus, errorThrown);
        },
        complete: function () {
            modal.find('.modal-form-buttons .btn').prop('disabled', false);
            delete MailVotech.activeActions[requestName];
        }
    });
};

MailVotech.activateIntegrationFieldUpdateActions = function () {
    mQuery('.integration-mapped-field').each(function() {
        mQuery(this).off("change.integration-mapped-field").on("change.integration-mapped-field", function (event) {
            var integration = mQuery(this).attr('data-integration');
            var object = mQuery(this).attr('data-object');
            var field = mQuery(this).attr('data-field');
            MailVotech.updateIntegrationField(integration, object, field, 'mappedField', mQuery(this).val());
        });
    });

    mQuery('.integration-sync-direction').each(function() {
        mQuery(this).off("change.integration-sync-direction").on("change.integration-sync-direction", function (event) {
            var integration = mQuery(this).attr('data-integration');
            var object = mQuery(this).attr('data-object');
            var field = mQuery(this).attr('data-field');
            MailVotech.updateIntegrationField(integration, object, field, 'syncDirection', mQuery(this).val());
        });
    });
};

MailVotech.authorizeIntegration = function () {
    mQuery('#integration_details_in_auth').val(1);
    MailVotech.postForm(mQuery('form[name="integration_config"]'), 'loadIntegrationAuthWindow');
};