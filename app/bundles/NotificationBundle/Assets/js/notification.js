/** NotificationBundle **/
MailVotech.notificationOnLoad = function (container, response) {
    if (mQuery(container + ' #list-search').length) {
        MailVotech.activateSearchAutocomplete('list-search', 'notification');
    }

    MailVotech.activatePreviewPanelUpdate();
};

MailVotech.selectNotificationType = function(notificationType) {
    if (notificationType == 'list') {
        mQuery('#leadList').removeClass('hide');
        mQuery('#publishStatus').addClass('hide');
        mQuery('.page-header h3').text(mailvotechLang.newListNotification);
    } else {
        mQuery('#publishStatus').removeClass('hide');
        mQuery('#leadList').addClass('hide');
        mQuery('.page-header h3').text(mailvotechLang.newTemplateNotification);
    }

    mQuery('#notification_notificationType').val(notificationType);

    mQuery('body').removeClass('noscroll');

    mQuery('.notification-type-modal').remove();
    mQuery('.notification-type-modal-backdrop').remove();
};

MailVotech.standardNotificationUrl = function(options) {
    if (!options) {
        return;
    }

    var url = options.windowUrl;
    if (url) {
        var editEmailKey = '/notifications/edit/notificationId';
        var previewEmailKey = '/notifications/preview/notificationId';
        if (url.indexOf(editEmailKey) > -1 ||
            url.indexOf(previewEmailKey) > -1) {
            options.windowUrl = url.replace('notificationId', mQuery('#campaignevent_properties_notification').val());
        }
    }

    return options;
};

MailVotech.disabledNotificationAction = function(opener) {
    if (typeof opener == 'undefined') {
        opener = window;
    }

    var notification = opener.mQuery('#campaignevent_properties_notification').val();

    var disabled = notification === '' || notification === null;

    opener.mQuery('#campaignevent_properties_editNotificationButton').prop('disabled', disabled);
};

MailVotech.activatePreviewPanelUpdate = function () {
    var notificationPreview = mQuery('#notification-preview');
    var notificationForm    = mQuery('form[name="notification"]');

    if (notificationPreview.length && notificationForm.length) {
        var inputs = notificationForm.find('input,textarea');

        inputs.on('blur', function () {
            var $this = mQuery(this);
            var name  = $this.attr('name');

            if (name === 'notification[heading]') {
                notificationPreview.find('h4').text($this.val());
            }

            if (name === 'notification[message]') {
                notificationPreview.find('p').text($this.val());
            }

            if (name === 'notification[url]') {
                notificationPreview.find('span').not('.ri-notification-3-fill').text($this.val());
            }
        });
    }
};