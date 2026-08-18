/* PluginBundle */
MailVotech.matchedFields = function (index, object, integration) {
    var compoundMailVotechFields = ['mailvotechContactId','mailvotechContactTimelineLink'];

    if (mQuery('#integration_details_featureSettings_updateDncByDate_0').is(':checked')) {
        compoundMailVotechFields.push('mailvotechContactIsContactableByEmail');
    }
    var integrationField = mQuery('#integration_details_featureSettings_'+object+'Fields_i_' + index).attr('data-value');
    var mailvotechField = mQuery('#integration_details_featureSettings_'+object+'Fields_m_' + index + ' option:selected').val();

    if(mQuery('.btn-arrow' + index).parent().attr('data-force-direction') != 1) {
        if (mQuery.inArray(mailvotechField, compoundMailVotechFields) >= 0) {
            mQuery('.btn-arrow' + index).removeClass('active');
            mQuery('#integration_details_featureSettings_' + object + 'Fields_update_mailvotech' + index + '_0').attr('checked', 'checked');
            mQuery('input[name="integration_details[featureSettings][' + object + 'Fields][update_mailvotech' + index + ']"]').prop('disabled', true).trigger("chosen:updated");
            mQuery('.btn-arrow' + index).addClass('disabled');
        }
        else {
            mQuery('input[name="integration_details[featureSettings][' + object + 'Fields][update_mailvotech' + index + ']"]').prop('disabled', false).trigger("chosen:updated");
            mQuery('.btn-arrow' + index).removeClass('disabled');
        }
    }

    if (object == 'lead') {
        var updateMailVotechField = mQuery('input[name="integration_details[featureSettings]['+object+'Fields][update_mailvotech' + index + ']"]:checked').val();
    } else {
        var updateMailVotechField = mQuery('input[name="integration_details[featureSettings]['+object+'Fields][update_mailvotech_company' + index + ']"]:checked').val();
    }
    MailVotech.ajaxActionRequest('plugin:matchFields', {object: object, integration: integration, integrationField : integrationField, mailvotechField: mailvotechField, updateMailVotech : updateMailVotechField}, function(response) {
        var theMessage = (response.success) ? '<i class="ri-check-line-circle text-success"></i>' : '';
        mQuery('#matched-' + index + "-" + object).html(theMessage);
    });
};
MailVotech.initiateIntegrationAuthorization = function() {
    mQuery('#integration_details_in_auth').val(1);

    MailVotech.postForm(mQuery('form[name="integration_details"]'), 'loadIntegrationAuthWindow');
};

MailVotech.loadIntegrationAuthWindow = function(response) {
    if (response.newContent) {
        MailVotech.processModalContent(response, '#IntegrationEditModal');
    } else {
        MailVotech.stopPageLoadingBar();
        MailVotech.stopIconSpinPostEvent();
        mQuery('#integration_details_in_auth').val(0);

        if (response.authUrl) {
            var generator = window.open(response.authUrl, 'integrationauth', 'height=500,width=500');

            if (!generator || generator.closed || typeof generator.closed == 'undefined') {
                alert(mailvotechLang.popupBlockerMessage);
            }
        }
    }
};

MailVotech.refreshIntegrationForm = function() {
    var opener = window.opener;
    if(opener) {
            var form = opener.mQuery('form[name="integration_details"]');
            if (form.length) {
                var action = form.attr('action');
                if (action) {
                    opener.MailVotech.startModalLoadingBar('#IntegrationEditModal');
                    opener.MailVotech.loadAjaxModal('#IntegrationEditModal', action);
                }
            }
    }

    window.close()
};

MailVotech.integrationOnLoad = function(container, response) {
    if (response && response.name) {
        var integration = '.integration-' + response.name;
        if (response.enabled) {
            mQuery(integration).removeClass('integration-disabled');
        } else {
            mQuery(integration).addClass('integration-disabled');
        }
    } else {
        MailVotech.filterIntegrations();
    }
    mQuery('[data-toggle="tooltip"]').tooltip();
};

MailVotech.integrationConfigOnLoad = function(container) {
    if (mQuery('.fields-container select.integration-field').length) {
        var selects = mQuery('.fields-container select.integration-field');
        selects.on('change', function() {
            var select   = mQuery(this),
                newValue = select.val(),
                previousValue = select.attr('data-value');
            select.attr('data-value', newValue);

            var groupSelects = mQuery(this).closest('.fields-container').find('select.integration-field').not(select);

            // Enable old value
            if (previousValue) {
                mQuery('option[value="' + previousValue + '"]', groupSelects).each(function() {
                    if (!mQuery(this).closest('select').prop('disabled')) {
                        mQuery(this).prop('disabled', false);
                        mQuery(this).removeAttr('disabled');
                    }
                });
            }

            if (newValue) {
                mQuery('option[value="' + newValue + '"]', groupSelects).each(function() {
                    if (!mQuery(this).closest('select').prop('disabled')) {
                        mQuery(this).prop('disabled', true);
                        mQuery(this).attr('disabled', 'disabled');
                    }
                });
            }

            groupSelects.each(function() {
                mQuery(this).trigger('chosen:updated');
            });
        });

        selects.each(function() {
            if (!mQuery(this).closest('.field-container').hasClass('hide')) {
                mQuery(this).trigger('change');
            }
        });
    }
};

MailVotech.filterIntegrations = function(update) {
    var filter = mQuery('#integrationFilter').val();

    if (update) {
        mQuery.ajax({
            url: mailvotechAjaxUrl,
            type: "POST",
            data: "action=plugin:setIntegrationFilter&plugin=" + filter
        });
    }

    //activate shuffles
    if (mQuery('.native-integrations').length) {
        //give a slight delay in order for images to load so that shuffle starts out with correct dimensions
        setTimeout(function () {
            var Shuffle = window.Shuffle,
                element = document.querySelector('.native-integrations'),
                shuffleOptions = {
                    itemSelector: '.shuffle-item'
                };

            // Using global variable to make it available outside of the scope of this function
            window.nativeIntegrationsShuffleInstance = new Shuffle(element, shuffleOptions);

            window.nativeIntegrationsShuffleInstance.filter(function($el) {
                if (filter) {
                    return mQuery($el).hasClass('plugin' + filter);
                } else {
                    // Shuffle.js has a bug. It hides the first item when we reset the filter.
                    // This fixes it.
                    mQuery(shuffleOptions.itemSelector).first().css('transform', '');
                    return true;
                }
            });

            // Update shuffle on sidebar minimize/maximize
            mQuery("html")
                .on("fa.sidebar.minimize", function() {
                    setTimeout(function() {
                        window.nativeIntegrationsShuffleInstance.update();
                    }, 1000);
                })
                .on("fa.sidebar.maximize", function() {
                    setTimeout(function() {
                        window.nativeIntegrationsShuffleInstance.update();
                    }, 1000);
                });

            // This delay is needed so that the tab has time to render and the sizes are correctly calculated
            mQuery('#plugin-nav-tabs a').click(function () {
                setTimeout(function() {
                    window.nativeIntegrationsShuffleInstance.update();
                }, 500);
            });
        }, 500);
    }
};

MailVotech.getIntegrationLeadFields = function (integration, el, settings) {

    if (typeof settings == 'undefined') {
        settings = {};
    }
    settings.integration = integration;
    settings.object      = 'lead';

    MailVotech.getIntegrationFields(settings, 1, el);
};

MailVotech.getIntegrationCompanyFields = function (integration, el, settings) {
    if (typeof settings == 'undefined') {
        settings = {};
    }
    settings.integration = integration;
    settings.object      = 'company';

    MailVotech.getIntegrationFields(settings, 1, el);
};

MailVotech.getIntegrationFields = function(settings, page, el) {
    var object    = settings.object ? settings.object : 'lead';
    var fieldsTab = ('lead' === object) ? '#fields-tab' : '#'+object+'-fields-container';

    if (el && mQuery(el).is('input')) {
        MailVotech.activateLabelLoadingIndicator(mQuery(el).attr('id'));

        var namePrefix = mQuery(el).attr('name').split('[')[0];
        if ('integration_details' !== namePrefix) {
            var nameParts = mQuery(el).attr('name').match(/\[.*?\]+/g);
            nameParts = nameParts.slice(0, -1);
            settings.prefix = namePrefix + nameParts.join('') + "[" + object + "Fields]";
        }
    }
    var fieldsContainer = '#'+object+'FieldsContainer';

    var inModal = mQuery(fieldsContainer).closest('.modal');
    if (inModal) {
        var modalId = '#'+mQuery(fieldsContainer).closest('.modal').attr('id');
        MailVotech.startModalLoadingBar(modalId);
    }

    MailVotech.ajaxActionRequest('plugin:getIntegrationFields',
        {
            page: page,
            integration: (settings.integration) ? settings.integration : null,
            settings: settings
        },
        function(response) {
            if (response.success) {
                mQuery(fieldsContainer).replaceWith(response.html);
                MailVotech.onPageLoad(fieldsContainer);
                MailVotech.integrationConfigOnLoad(fieldsContainer);
                if (mQuery(fieldsTab).length) {
                    mQuery(fieldsTab).removeClass('hide');
                }
            } else {
                if (mQuery(fieldsTab).length) {
                    mQuery(fieldsTab).addClass('hide');
                }
            }

            if (el) {
                MailVotech.removeLabelLoadingIndicator();
            }

            if (inModal) {
                MailVotech.stopModalLoadingBar(modalId);
            }
        }
    );
};

MailVotech.getIntegrationConfig = function (el, settings) {
    MailVotech.activateLabelLoadingIndicator(mQuery(el).attr('id'));

    if (typeof settings == 'undefined') {
        settings = {};
    }

    settings.name = mQuery(el).attr('name');
    var data = {integration: mQuery(el).val(), settings: settings};
    mQuery('.integration-campaigns-status').html('');
    mQuery('.integration-config-container').html('');

    MailVotech.ajaxActionRequest('plugin:getIntegrationConfig', data,
        function (response) {
            if (response.success) {
                mQuery('.integration-config-container').html(response.html);
                MailVotech.onPageLoad('.integration-config-container', response);
            }

            MailVotech.integrationConfigOnLoad('.integration-config-container');
            MailVotech.removeLabelLoadingIndicator();
        },
        false,
        false,
        "GET"
    );


};

MailVotech.getIntegrationCampaignStatus = function (el, settings) {
    MailVotech.activateLabelLoadingIndicator(mQuery(el).attr('id'));
    if (typeof settings == 'undefined') {
        settings = {};
    }

    // Extract the name and ID prefixes
    var prefix = mQuery(el).attr('name').split("[")[0];

    settings.name = mQuery('#'+prefix+'_properties_integration').attr('name');
    var data = {integration:mQuery('#'+prefix+'_properties_integration').val(),campaign: mQuery(el).val(), settings: settings};

    mQuery('.integration-campaigns-status').html('');
    mQuery('.integration-campaigns-status').removeClass('hide');
    MailVotech.ajaxActionRequest('plugin:getIntegrationCampaignStatus', data,
        function (response) {

            if (response.success) {
                mQuery('.integration-campaigns-status').append(response.html);
                MailVotech.onPageLoad('.integration-campaigns-status', response);
            }

            MailVotech.integrationConfigOnLoad('.integration-campaigns-status');
            MailVotech.removeLabelLoadingIndicator();
        },
        false,
        false,
        "GET"
    );
};

MailVotech.initPluginEvents = function () {
    const $integrationModal = mQuery('#IntegrationEditModal');

    $integrationModal.off('mailvotech:onPageLoad:before');
    $integrationModal.on('mailvotech:onPageLoad:before', function(e, container, response) {
        if (container === '#IntegrationEditModal' && response && response.pluginVersion) {
            const $modalLabel = mQuery('#IntegrationEditModal-label');
            if ($modalLabel.find('.plugin-version-badge').length === 0) {
                const $badge = mQuery('<span>')
                    .addClass('plugin-version-badge label label-default ml-xs')
                    .text('v' + String(response.pluginVersion));

                $modalLabel.append(' ').append($badge);
            }
        }
    });
};