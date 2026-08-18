//StageBundle
MailVotech.stageOnLoad = function (container, response) {
    const sequence = mQuery('#stage-weight-sequence');

    if (!sequence.length) {
        return;
    }

    const stageWeights = sequence.data('weights');
    const weightFieldId = sequence.data('weight-field-id');

    if (stageWeights && weightFieldId) {
        const entityId = sequence.data('entity-id');
        MailVotech.initStageWeightConflictCheck(stageWeights, weightFieldId, entityId);
    }
};

MailVotech.getStageActionPropertiesForm = function(actionType) {
    MailVotech.activateLabelLoadingIndicator('stage_type');

    var query = "action=stage:getActionForm&actionType=" + actionType;
    mQuery.ajax({
        url: mailvotechAjaxUrl,
        type: "POST",
        data: query,
        dataType: "json",
        success: function (response) {
            if (typeof response.html != 'undefined') {
                mQuery('#stageActionProperties').html(response.html);
                MailVotech.onPageLoad('#stageActionProperties', response);
            }
        },
        error: function (request, textStatus, errorThrown) {
            MailVotech.processAjaxError(request, textStatus, errorThrown);
        },
        complete: function() {
            MailVotech.removeLabelLoadingIndicator();
        }
    });
};

MailVotech.initStageWeightConflictCheck = function(weights, weightFieldId, entityId) {
    mQuery(document).ready(function() {
        const weightField = mQuery('#' + weightFieldId);
        const group = weightField.closest('.form-group');
        const message = mQuery('<span class="help-block text-danger"></span>').insertAfter(weightField);

        function check() {
            const val = parseInt(weightField.val(), 10);
            const conflict = weights.some(function(w) {
                return w.weight == val && (!entityId || w.id != entityId);
            });

            if (conflict) {
                group.addClass('has-error');
                message.text(MailVotech.translate('mailvotech.stage.weight.conflict'));
            } else {
                group.removeClass('has-error');
                message.text('');
            }
        }

        weightField.on('input', check);
        check();
    });
};
