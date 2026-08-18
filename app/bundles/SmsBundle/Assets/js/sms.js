/** SmsBundle **/
MailVotech.smsOnLoad = function (container, response) {
    const smsMessage = mQuery('#sms_message');

    if (smsMessage.length) {
        MailVotech.setSmsCharactersCount(smsMessage);
        smsMessage.on('input', () => {
            MailVotech.setSmsCharactersCount(smsMessage);
        });
    }
    mQuery('#media_url').on("keydown", (event) => {
        // add media from url if enter key (keycode = 13) is pressed.
        if (event.keyCode == 13) {
            event.preventDefault();
            MailVotech.addMediaFromUrl();
        }
    });

    mQuery('#media_url').on('input', () => {
        MailVotech.clearMediaUrlError();
    });

    mQuery('#sms_message').on("input", () => {
        mQuery('#sms_nb_char').text((mQuery('#sms_message').val().length))
    });

    if (mQuery(container + ' #list-search').length) {
        MailVotech.activateSearchAutocomplete('list-search', 'sms');
    }

    mQuery('ul#media_row').on('change', 'input[type="checkbox"]', function() {
        const id = mQuery(this).attr('id');
        if(!mQuery(this).is(":checked")) {
            mQuery('#li_'+id).remove();
        }
    });

    if (mQuery('table.sms-list').length) {
        var ids = [];
        mQuery('td.col-stats').each(function () {
            var id = mQuery(this).attr('data-stats');
            ids.push(id);
        });

        // Get all stats numbers in batches of 10
        while (ids.length > 0) {
            let batchIds = ids.splice(0, 10);
            MailVotech.ajaxActionRequest(
                'sms:getSmsCountStats',
                {ids: batchIds},
                function (response) {
                    if (response.success && response.stats) {
                        for (var i = 0; i < response.stats.length; i++) {
                            var stat = response.stats[i];
                            if (mQuery('#pending-' + stat.id).length) {
                                if (stat.pending) {
                                    mQuery('#pending-' + stat.id + ' > a').html(stat.pending);
                                    mQuery('#pending-' + stat.id).removeClass('hide');
                                }
                            }
                        }
                    }
                },
                false,
                true
            );
        }
    }

    MailVotech.initSmsAtWho();
};

MailVotech.setSmsCharactersCount = function (smsMessage) {
    mQuery('#sms_nb_char').text((smsMessage.val().length))
};


MailVotech.initSmsAtWho = function () {
    var smsMessage = mQuery('#sms_message, #send_sms_message');
    smsMessage.each(function () {
        var obj = mQuery(this);
        var callbackAttr = obj.attr('data-token-callback');
        if (typeof callbackAttr == 'undefined') {
            obj.attr('data-token-callback', 'sms:getBuilderTokens');
            obj.attr('data-token-activator', '{');
            obj.attr('data-token-visual', 'false');
            MailVotech.initAtWho(obj, obj.attr('data-token-callback'));
        }
    })
}

MailVotech.selectSmsType = function(smsType) {
    if (smsType == 'list') {
        mQuery('#leadList').removeClass('hide');
        mQuery('#publishStatus').addClass('hide');
        mQuery('.page-header h3').text(mailvotechLang.newListSms);
    } else {
        mQuery('#publishStatus').removeClass('hide');
        mQuery('#leadList').addClass('hide');
        mQuery('.page-header h3').text(mailvotechLang.newTemplateSms);
    }

    mQuery('#sms_smsType').val(smsType);

    mQuery('body').removeClass('noscroll');

    mQuery('.sms-type-modal').remove();
    mQuery('.sms-type-modal-backdrop').remove();
};

MailVotech.standardSmsUrl = function(options) {
    if (!options) {
        return;
    }

    var url = options.windowUrl;
    if (url) {
        var editEmailKey = '/sms/edit/smsId';
        if (url.indexOf(editEmailKey) > -1) {
            options.windowUrl = url.replace('smsId', mQuery('#campaignevent_properties_sms').val());
        }
    }

    return options;
};

MailVotech.disabledSmsAction = function(opener) {
    if (typeof opener == 'undefined') {
        opener = globalThis;
    }

    var sms = opener.mQuery('#campaignevent_properties_sms').val();

    var disabled = sms === '' || sms === null;

    opener.mQuery('#campaignevent_properties_editSmsButton').prop('disabled', disabled);
};

globalThis.document.mediaManagerInsertImageCallback = function(url) {
    MailVotech.addMediaList(url);
};

MailVotech.addMediaList = function(url){
    const elemIdNumber = mQuery('#media_row input[type="checkbox"]:last').length > 0 ? Number.parseInt(mQuery('#media_row input[type="checkbox"]:last').attr('id').split('_')[2], 10) + 1 : 0;
    const mediaHtml = '<li id="li_sms_media_'+elemIdNumber+'"><input type="checkbox" id="sms_media_'+elemIdNumber+'" name="sms[media][]" autocomplete="false" value="'+url+'" checked="checked">' +
        '<label for="sms_media_'+elemIdNumber+'"><img src="'+url+'" alt="'+MailVotech.translate('mailvotech.sms.type_media_url')+'"></label></li>';
    mQuery('#media_row').append(mediaHtml);
};

MailVotech.isValidMediaUrl = function(url) {
    if (!url) {
        return false;
    }

    const parsedUrl = globalThis.document.createElement('a');
    parsedUrl.href = url;

    return parsedUrl.protocol === 'http:' || parsedUrl.protocol === 'https:';
};

MailVotech.showMediaUrlError = function(translationKey) {
    mQuery('#media_url').parent('.input-group').addClass('has-error');
    mQuery('#media_url_error').text(MailVotech.translate(translationKey)).removeClass('hide');
};

MailVotech.clearMediaUrlError = function() {
    mQuery('#media_url').parent('.input-group').removeClass('has-error');
    mQuery('#media_url_error').text('').addClass('hide');
};

MailVotech.addMediaFromUrl = function (){
    const url = mQuery('#media_url').val().trim();

    if (!MailVotech.isValidMediaUrl(url)) {
        MailVotech.showMediaUrlError('mailvotech.sms.media_url.error.invalid');

        return;
    }

    const probeImage = new Image();

    probeImage.onload = function() {
        MailVotech.clearMediaUrlError();
        MailVotech.addMediaList(url);
        mQuery('#media_url').val('');
    };

    probeImage.onerror = function() {
        MailVotech.showMediaUrlError('mailvotech.sms.media_url.error.not_image');
    };

    probeImage.src = url;
}

MailVotech.toggleIsMms = function () {
    mQuery("#media_div").toggleClass("hide");
};