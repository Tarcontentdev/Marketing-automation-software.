MailVotech.contentVersions = {};
MailVotech.versionNamespace = '';
MailVotech.currentContentVersion = -1;

/**
 * Setup versioning for the given namespace
 *
 * @param undoCallback function
 * @param redoCallback function
 * @param namespace
 */
MailVotech.prepareVersioning = function (undoCallback, redoCallback, namespace) {
    // Check if localStorage is supported and if not, disable undo/redo buttons
    if (!MailVotech.isLocalStorageSupported()) {
        mQuery('.btn-undo').prop('disabled', true);
        mQuery('.btn-redo').prop('disabled', true);

        return;
    }

    mQuery('.btn-undo')
        .prop('disabled', false)
        .on('click', function() {
            MailVotech.undoVersion(undoCallback);
        });

    mQuery('.btn-redo')
        .prop('disabled', false)
        .on('click', function() {
            MailVotech.redoVersion(redoCallback);
        });

    MailVotech.currentContentVersion = -1;

    if (!namespace) {
        namespace = window.location.href;
    }

    if (typeof MailVotech.contentVersions[namespace] == 'undefined') {
        MailVotech.contentVersions[namespace] = [];
    }

    MailVotech.versionNamespace = namespace;

    console.log(namespace);
};

/**
 * Clear versioning
 *
 * @param namespace
 */
MailVotech.clearVersioning = function () {
    if (!MailVotech.versionNamespace) {
        throw 'Versioning not configured';
    }

    if (typeof MailVotech.contentVersions[MailVotech.versionNamespace] !== 'undefined') {
        delete MailVotech.contentVersions[MailVotech.versionNamespace];
    }

    MailVotech.versionNamespace = '';
    MailVotech.currentContentVersion = -1;
};

/**
 * Store a version
 *
 * @param content
 */
MailVotech.storeVersion = function(content) {
    if (!MailVotech.versionNamespace) {
        throw 'Versioning not configured';
    }

    // Store the content
    MailVotech.contentVersions[MailVotech.versionNamespace].push(content);

    // Set the current location to the latest spot
    MailVotech.currentContentVersion = MailVotech.contentVersions[MailVotech.versionNamespace].length;
};

/**
 * Decrement a version
 *
 * @param callback
 */
MailVotech.undoVersion = function(callback) {
    console.log('undo');
    if (!MailVotech.versionNamespace) {
        throw 'Versioning not configured';
    }

    if (MailVotech.currentContentVersion < 0) {
        // Nothing to undo

        return;
    }

    var version = MailVotech.currentContentVersion - 1;
    if (MailVotech.getVersion(version, callback)) {
        --MailVotech.currentContentVersion;
    };
};

/**
 * Increment a version
 *
 * @param callback
 */
MailVotech.redoVersion = function(callback) {
    console.log('redo');
    if (!MailVotech.versionNamespace) {
        throw 'Versioning not configured';
    }

    if (MailVotech.currentContentVersion < 0 || MailVotech.contentVersions[MailVotech.versionNamespace].length === MailVotech.currentContentVersion) {
        // Nothing to redo

        return;
    }

    var version = MailVotech.currentContentVersion + 1;
    if (MailVotech.getVersion(version, callback)) {
        ++MailVotech.currentContentVersion;
    };
};

/**
 * Check for a given version and execute callback
 *
 * @param version
 * @param command
 * @returns {boolean}
 */
MailVotech.getVersion = function(version, callback) {
    var content = false;
    if (typeof MailVotech.contentVersions[MailVotech.versionNamespace][version] !== 'undefined') {
        content = MailVotech.contentVersions[MailVotech.versionNamespace][version];
    }

    if (false !== content && typeof callback == 'function') {
        callback(content);

        return true;
    }

    return false;
};