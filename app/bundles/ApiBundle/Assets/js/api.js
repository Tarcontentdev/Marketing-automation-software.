//ApiBundle
MailVotech.clientOnLoad = function (container) {
    if (mQuery(container + ' #list-search').length) {
        MailVotech.activateSearchAutocomplete('list-search', 'api.client');
    }
};

MailVotech.refreshApiClientForm = function(url, modeEl) {
    var mode = mQuery(modeEl).val();

    if (mQuery('#client_redirectUris').length) {
        mQuery('#client_redirectUris').prop('disabled', true);
    } else {
        mQuery('#client_callback').prop('disabled', true);
    }
    mQuery('#client_name').prop('disabled', true);

    MailVotech.loadContent(url + '/' + mode);
};