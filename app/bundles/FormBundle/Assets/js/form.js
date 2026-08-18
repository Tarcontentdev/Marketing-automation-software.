//FormBundle
MailVotech.formOnLoad = function (container) {

    if (mQuery(container + ' #list-search').length) {
        MailVotech.activateSearchAutocomplete('list-search', 'form.form');
    }

    MailVotech.toggleThemeSelectorVisibility();
    mQuery('#mailvotechform_renderStyle_0, #mailvotechform_renderStyle_1').on('change', MailVotech.toggleThemeSelectorVisibility);

    MailVotech.formBuilderNewComponentInit();
    MailVotech.iniNewConditionalField();

    var bodyOverflow = {};

    if (mQuery('#mailvotechforms_fields')) {
        //make the fields sortable
        mQuery('#mailvotechforms_fields').sortable({
            items: '.form-field-wrapper',
            cancel: '',
            helper: function(e, ui) {
                ui.children().each(function() {
                    mQuery(this).width(mQuery(this).width());
                });

                // Fix body overflow that messes sortable up
                bodyOverflow.overflowX = mQuery('body').css('overflow-x');
                bodyOverflow.overflowY = mQuery('body').css('overflow-y');
                mQuery('body').css({
                    overflowX: 'visible',
                    overflowY: 'visible'
                });

                return ui;
            },
            scroll: true,
            axis: 'y',
            containment: '#mailvotechforms_fields .drop-here',
            stop: function(e, ui) {
                // Restore original overflow
                mQuery('body').css(bodyOverflow);
                mQuery(ui.item).attr('style', '');

                mQuery.ajax({
                    type: "POST",
                    url: mailvotechAjaxUrl + "?action=form:reorderFields",
                    data: mQuery('#mailvotechforms_fields').sortable("serialize", {attribute: 'data-sortable-id'}) + "&formId=" + mQuery('#mailvotechform_sessionId').val()
                });
            }
        });

        MailVotech.initFormFieldButtons();
    }

    if (mQuery('#mailvotechforms_actions')) {
        //make the fields sortable
        mQuery('#mailvotechforms_actions').sortable({
            items: '.panel',
            cancel: '',
            helper: function(e, ui) {
                ui.children().each(function() {
                    mQuery(this).width(mQuery(this).width());
                });

                // Fix body overflow that messes sortable up
                bodyOverflow.overflowX = mQuery('body').css('overflow-x');
                bodyOverflow.overflowY = mQuery('body').css('overflow-y');
                mQuery('body').css({
                    overflowX: 'visible',
                    overflowY: 'visible'
                });

                return ui;
            },
            scroll: true,
            axis: 'y',
            containment: '#mailvotechforms_actions .drop-here',
            stop: function(e, ui) {
                // Restore original overflow
                mQuery('body').css(bodyOverflow);
                mQuery(ui.item).attr('style', '');

                mQuery.ajax({
                    type: "POST",
                    url: mailvotechAjaxUrl + "?action=form:reorderActions",
                    data: mQuery('#mailvotechforms_actions').sortable("serialize") + "&formId=" + mQuery('#mailvotechform_sessionId').val()
                });
            }
        });

        mQuery('#mailvotechforms_actions .mailvotechform-row').on('dblclick.mailvotechformactions', function(event) {
            event.preventDefault();
            mQuery(this).find('.btn-edit').first().click();
        });
    }

    MailVotech.initHideItemButton('#mailvotechforms_fields');
    MailVotech.initHideItemButton('#mailvotechforms_actions');
};

MailVotech.formBuilderNewComponentInit = function () {
    mQuery('select.form-builder-new-component:not(.initialized)').change(function (e) {
        const select = mQuery(this);
        select.addClass('initialized');
        select.find('option:selected');
        MailVotech.ajaxifyModal(select.find('option:selected'));
        // Reset the dropdown
        select.val('');
        select.chosen('destroy').chosen();
    });
};

MailVotech.changeSelectOptions = function(selectEl, options) {
    selectEl.empty();
    mQuery.each(options, function(key, field) {
        selectEl.append(
            mQuery('<option></option>')
                .attr('value', field.value)
                .attr('data-list-type', field.isListType ? 1 : 0)
                .text(field.label)
        );
    });
    selectEl.trigger('chosen:updated');
};

MailVotech.fetchFieldsOnObjectChange = function() {
    var fieldSelect = mQuery('select#formfield_mappedField');
    fieldSelect.attr('disable', true);
    mQuery.ajax({
        url: mailvotechAjaxUrl + "?action=form:getFieldsForObject",
        data: {
            mappedObject: mQuery('select#formfield_mappedObject').val(),
            mappedField: mQuery('input#formfield_originalMappedField').val(),
            formId: mQuery('input#mailvotechform_sessionId').val()
        },
        success: function (response) {
            MailVotech.changeSelectOptions(fieldSelect, response.fields);
        },
        error: function (response, textStatus, errorThrown) {
            MailVotech.processAjaxError(response, textStatus, errorThrown);
        },
        complete: function () {
            fieldSelect.removeAttr('disable');
        }
    });
};

MailVotech.formResultBatchSubmit = function () {
    if (!mQuery('#lead_batch_ids').val()) {
        return false;
    }

    return mQuery('#lead_batch_add').val() || mQuery('#lead_batch_remove').val();
};

MailVotech.updateFormFields = function () {
    MailVotech.activateLabelLoadingIndicator('campaignevent_properties_field');

    var formId = mQuery('#campaignevent_properties_form').val();
    MailVotech.ajaxActionRequest('form:updateFormFields', {'formId': formId}, function(response) {
        if (response.fields) {
            var select = mQuery('#campaignevent_properties_field');
            select.find('option').remove();
            var fieldOptions = {};
            mQuery.each(response.fields, function(key, field) {
                var option = mQuery('<option></option>')
                    .attr('value', field.alias)
                    .text(field.label);
                select.append(option);
                fieldOptions[field.alias] = field.options;
            });
            select.attr('data-field-options', JSON.stringify(fieldOptions));
            select.trigger('chosen:updated');
            MailVotech.updateFormFieldValues(select);
        }
        MailVotech.removeLabelLoadingIndicator();
    });
};

MailVotech.updateFormFieldValues = function (field) {
    field = mQuery(field);
    var fieldValue = field.val();
    var options = jQuery.parseJSON(field.attr('data-field-options'));
    var valueField = mQuery('#campaignevent_properties_value');
    var valueFieldAttrs = {
        'class': valueField.attr('class'),
        'id': valueField.attr('id'),
        'name': valueField.attr('name'),
        'autocomplete': valueField.attr('autocomplete'),
        'value': valueField.attr('value')
    };

    if (typeof options[fieldValue] !== 'undefined' && !mQuery.isEmptyObject(options[fieldValue])) {
        var newValueField = mQuery('<select/>')
            .attr('class', valueFieldAttrs['class'])
            .attr('id', valueFieldAttrs['id'])
            .attr('name', valueFieldAttrs['name'])
            .attr('autocomplete', valueFieldAttrs['autocomplete'])
            .attr('value', valueFieldAttrs['value']);
        mQuery.each(options[fieldValue], function(key, optionVal) {
            var option = mQuery("<option></option>")
                .attr('value', key)
                .text(optionVal);
            newValueField.append(option);
        });
        valueField.replaceWith(newValueField);
    } else {
        var newValueField = mQuery('<input/>')
            .attr('type', 'text')
            .attr('class', valueFieldAttrs['class'])
            .attr('id', valueFieldAttrs['id'])
            .attr('name', valueFieldAttrs['name'])
            .attr('autocomplete', valueFieldAttrs['autocomplete'])
            .attr('value', valueFieldAttrs['value']);
        valueField.replaceWith(newValueField);
    }
};

MailVotech.formFieldOnLoad = function (container, response) {
    //new field created so append it to the form
    if (response.fieldHtml) {
        var newHtml = response.fieldHtml;
        var fieldId = '#mailvotechform_' + response.fieldId;
        var fieldContainer = mQuery(fieldId).closest('.form-field-wrapper');

        if (mQuery(fieldId).length) {
            //replace content
            mQuery(fieldContainer).replaceWith(newHtml);
            var newField = false;
        } else {
            var parentContainer = mQuery('#mailvotechform_'+response.parent);
            if (parentContainer.length) {
                (parentContainer.parents('.panel:first')).append(newHtml);
            }else {
                //append content
                var panel = mQuery('#mailvotechforms_fields .mailvotechform-button-wrapper').closest('.form-field-wrapper');
                panel.before(newHtml);
            }
            var newField = true;
        }

        // Get the updated element
        var fieldContainer = mQuery(fieldId).closest('.form-field-wrapper');

        //activate new stuff
        mQuery(fieldContainer).find("[data-toggle='ajax']").click(function (event) {
            event.preventDefault();
            return MailVotech.ajaxifyLink(this, event);
        });

        //initialize tooltips
        mQuery(fieldContainer).find("*[data-toggle='tooltip']").tooltip({html: true});

        //initialize ajax'd modals
        mQuery(fieldContainer).find("[data-toggle='ajaxmodal']").on('click.ajaxmodal', function (event) {
            event.preventDefault();
            MailVotech.ajaxifyModal(this, event);
        });

        MailVotech.initFormFieldButtons(fieldContainer);
        MailVotech.initHideItemButton(fieldContainer);

        //show fields panel
        if (!mQuery('#fields-panel').hasClass('in')) {
            mQuery('a[href="#fields-panel"]').trigger('click');
        }

        if (newField) {
            mQuery('.bundle-main-inner-wrapper').scrollTop(mQuery('.bundle-main-inner-wrapper').height());
        }

        if (mQuery('#form-field-placeholder').length) {
            mQuery('#form-field-placeholder').remove();
        }

        MailVotech.activateChosenSelect(mQuery('.form-builder-new-component'));
        MailVotech.formBuilderNewComponentInit();
        MailVotech.iniNewConditionalField();
    }
};

MailVotech.iniNewConditionalField = function(){
    mQuery('.add-new-conditional-field').click(function (e) {
        e.preventDefault();
        mQuery(this).parent().next().show('normal');
    })
    mQuery('.add-new-conditional-field').parent().next().hide();

}

MailVotech.initFormFieldButtons = function (container) {
    if (typeof container == 'undefined') {
        mQuery('#mailvotechforms_fields .mailvotechform-row').off(".mailvotechformfields");
        var container = '#mailvotechforms_fields';
    }

    mQuery(container).find('.mailvotechform-row').on('dblclick.mailvotechformfields', function(event) {
        event.preventDefault();
        mQuery(this).closest('.form-field-wrapper').find('.btn-edit').first().click();
    });
};

MailVotech.formActionOnLoad = function (container, response) {
    //new action created so append it to the form
    if (response.actionHtml) {
        var newHtml = response.actionHtml;
        var actionId = '#mailvotechform_action_' + response.actionId;
        if (mQuery(actionId).length) {
            //replace content
            mQuery(actionId).replaceWith(newHtml);
            var newField = false;
        } else {
            //append content
            mQuery(newHtml).appendTo('#mailvotechforms_actions .drop-here');
            var newField = true;
        }
        //activate new stuff
        mQuery(actionId + " [data-toggle='ajax']").click(function (event) {
            event.preventDefault();
            return MailVotech.ajaxifyLink(this, event);
        });
        //initialize tooltips
        mQuery(actionId + " *[data-toggle='tooltip']").tooltip({html: true});

        //initialize ajax'd modals
        mQuery(actionId + " [data-toggle='ajaxmodal']").on('click.ajaxmodal', function (event) {
            event.preventDefault();

            MailVotech.ajaxifyModal(this, event);
        });

        MailVotech.initHideItemButton(actionId);

        mQuery('#mailvotechforms_actions .mailvotechform-row').off(".mailvotechform");
        mQuery('#mailvotechforms_actions .mailvotechform-row').on('dblclick.mailvotechformactions', function(event) {
            event.preventDefault();
            mQuery(this).find('.btn-edit').first().click();
        });

        //show actions panel
        if (!mQuery('#actions-panel').hasClass('in')) {
            mQuery('a[href="#actions-panel"]').trigger('click');
        }

        if (newField) {
            mQuery('.bundle-main-inner-wrapper').scrollTop(mQuery('.bundle-main-inner-wrapper').height());
        }

        if (mQuery('#form-action-placeholder').length) {
            mQuery('#form-action-placeholder').remove();
        }
    }
};

MailVotech.initHideItemButton = function(container) {
    mQuery(container).find('[data-hide-panel]').click(function(e) {
        e.preventDefault();
        mQuery(this).closest('.form-field-wrapper, .mailvotechform-row').hide('fast');
    });
}

MailVotech.onPostSubmitActionChange = function(value) {
    if (value == 'return') {
        //remove required class
        mQuery('#mailvotechform_postActionProperty').prev().removeClass('required');
    } else {
        mQuery('#mailvotechform_postActionProperty').prev().addClass('required');
    }

    mQuery('#mailvotechform_postActionProperty').next().html('');
    mQuery('#mailvotechform_postActionProperty').parent().removeClass('has-error');
};

/**
 * @deprecated since MailVotech 7.1, to be removed in 8.0 with no replacement.
 * @param formType
 */
MailVotech.selectFormType = function(formType) {
    mQuery('#mailvotechform_formType').val(formType);
};

/**
 * Toggles theme selection field visibility and manages theme selection
 */
MailVotech.toggleThemeSelectorVisibility = function () {
    var selectField = mQuery('#mailvotechform_template');
    var chosenContainer = mQuery('#mailvotechform_template_chosen');

    if (mQuery('#mailvotechform_renderStyle_0').prop('checked')) {
        selectField.val('').trigger('chosen:updated');
        chosenContainer.addClass('chosen-disabled');
    } else {
        chosenContainer.removeClass('chosen-disabled');
    }
};
