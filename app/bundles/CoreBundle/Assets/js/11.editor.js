MailVotech.builderTokensForCkEditor = {};
MailVotech.builderTokens = {};
MailVotech.dynamicContentTokens = {};
MailVotech.builderTokensRequestInProgress = false;
MailVotech.imageManagerLoadURL = mailvotechBaseUrl + 's/file/list';
MailVotech.imageUploadURL = mailvotechBaseUrl + 's/file/upload';
MailVotech.imageManagerDeleteURL = mailvotechBaseUrl + 's/file/delete';
MailVotech.elfinderURL = mailvotechBaseUrl + 'elfinder';

/**
 * Initialize AtWho dropdown.
 *
 * @param element jQuery element
 * @param method  method to get the tokens from
 */
MailVotech.initAtWho = function(element, method) {
    // Avoid to request the tokens if not necessary
    if (MailVotech.builderTokensRequestInProgress) {
        // Wait till previous request finish
        var intervalID = setInterval(function(){
            if (!MailVotech.builderTokensRequestInProgress) {
                clearInterval(intervalID);
                MailVotech.configureAtWho(element, method);
            }
        }, 500);
    } else {
        MailVotech.configureAtWho(element, method);
    }
};

/**
 * Initialize AtWho dropdown.
 *
 * @param element jQuery element
 * @param method  method to get the tokens from
 */
MailVotech.configureAtWho = function(element, method) {
    MailVotech.getTokens(method, function(tokens) {
        element.atwho('destroy');

        // Add the dynamic content tokens
        mQuery.extend(tokens, MailVotech.dynamicContentTokens);

        element.atwho({
            at: '{',
            displayTpl: '<li>${name} <small>${id}</small></li>',
            insertTpl: "${id}",
            editableAtwhoQueryAttrs: {"data-fr-verified": true},
            data: mQuery.map(tokens, function(value, i) {
                return {'id':i, 'name':value};
            }),
            acceptSpaceBar: true
        });
    });
};

/**
 * Download the tokens
 *
 * @param method to fetch the tokens from
 * @param callback(tokens) to call when finished
 */
MailVotech.getTokens = function(method, callback) {
    // Check if the builderTokens var holding the tokens was already loaded
    if (!mQuery.isEmptyObject(MailVotech.builderTokens)) {
        return callback(MailVotech.builderTokens);
    }

    MailVotech.builderTokensRequestInProgress = true;

    // OK, let's fetch the tokens.
    mQuery.ajax({
        url: mailvotechAjaxUrl,
        data: 'action=' + method,
        success: function (response) {
            if (typeof response.tokens === 'object') {

                // store the tokens to the session storage
                MailVotech.builderTokens = response.tokens;

                // return the callback with tokens
                callback(response.tokens);
            }
        },
        error: function (request, textStatus, errorThrown) {
            MailVotech.processAjaxError(request, textStatus, errorThrown);
        },
        complete: function() {
            MailVotech.builderTokensRequestInProgress = false;
        }
    });
};

MailVotech.insertHtmlInEditor = function (obj, html) {
    const ckEditor = ckEditors.get(obj[0]);
    const viewFragment = ckEditor.data.processor.toView(html);
    const modelFragment = ckEditor.data.toModel(viewFragment);

    ckEditor.model.change(writer => {
        const insertPosition = ckEditor.model.document.selection.getFirstPosition();
        writer.insert(modelFragment, insertPosition);
    });
};

MailVotech.MentionLinks =  function (editor) {

    editor.conversion.for( 'upcast' ).elementToAttribute( {
        view: {
            name: 'span',
            key: 'data-fr-verified',
            classes: 'atwho-inserted'
        },
        model: {
            key: 'mention',
            value: viewItem => editor.plugins.get( 'Mention' ).toMentionAttribute( viewItem )
        },
        converterPriority: 'high'
    } );

    editor.conversion.for( 'downcast' ).attributeToElement( {
        model: 'mention',
        view: ( modelAttributeValue, { writer } ) => {
            if ( !modelAttributeValue ) {
                return;
            }

            return writer.createAttributeElement( 'span', {
                class: 'atwho-inserted',
                'data-fr-verified': true
            }, {
                priority: 20,
                id: modelAttributeValue.uid
            } );
        },
        converterPriority: 'high'

    } );
}

/*
 * Customizes the way the list of user suggestions is displayed.
 *
 * @deprecated: will be removed in M6
 */
MailVotech.customItemRenderer = function (item) {
    let tokenId = item.id;
    let tokenName = item.name;
    const itemElement = document.createElement( 'span' );
    const idElement = document.createElement( 'span' );
    idElement.classList.add( 'custom-item-id' );
    itemElement.classList.add( 'custom-item' );

    if (tokenName.startsWith('a:')) {
        tokenName = tokenName.substring(2);
    }

    itemElement.textContent = tokenName;
    idElement.textContent = tokenId;
    itemElement.appendChild( idElement );
    return itemElement;
}

/*
 * @deprecated: will be removed in M6
 */
MailVotech.getFeedItems = function (queryText) {
    return new Promise( resolve => {
        setTimeout( () => {
            const itemsToDisplay = MailVotech.builderTokensForCkEditor
                .filter( isItemMatching )
                .slice( 0, 5 );
            resolve( itemsToDisplay );
        }, 100 );
    } );

    function isItemMatching(item) {
        const searchString = queryText.toLowerCase();
        return (
            item.name.toLowerCase().includes( searchString ) ||
            item.id.toLowerCase().includes( searchString )
        );
    }
}

MailVotech.getTokensForPlugIn = function(method) {
    method = typeof method != 'undefined' ? method : 'page:getBuilderTokens';
    // OK, let's fetch the tokens.
    mQuery.ajax({
        url: mailvotechAjaxUrl,
        data: 'action=' + method,
        async: false,
        success: function (response) {
            if (typeof response.tokens === 'object') {
                MailVotech.builderTokens = response.tokens;
                mQuery.extend(MailVotech.builderTokens, MailVotech.dynamicContentTokens);
                MailVotech.builderTokensForCkEditor = mQuery.map(MailVotech.builderTokens, function(value, i) {
                    return {'id':i, 'name':value};
                });
            }
        },
        error: function (request, textStatus, errorThrown) {
            MailVotech.processAjaxError(request, textStatus, errorThrown);
        },
        complete: function() {
            MailVotech.builderTokensRequestInProgress = false;
        }
    });
    return MailVotech.builderTokensForCkEditor;
};

MailVotech.getCKEditorFonts = function(fonts) {
    fonts = Array.isArray(fonts) ? fonts : [];
    const CKEditorFonts = [];

    for (let i = 0; i < fonts.length; i++) {
        if ('undefined' != typeof fonts[i].font) {
            CKEditorFonts.push(fonts[i].font);
        }
    }

    return CKEditorFonts;
}

MailVotech.ConvertFieldToCkeditor  = function(textarea, ckEditorToolbarOptions) {
    if (ckEditors.has( textarea[0] ))
    {
        ckEditors.get( textarea[0] ).destroy();
        ckEditors.delete( textarea[0] )
    }
    const tokenCallback = textarea.attr('data-token-callback');
    MailVotech.InitCkEditor(textarea, MailVotech.GetCkEditorConfigOptions(ckEditorToolbarOptions, tokenCallback, textarea));
}

MailVotech.GetCkEditorConfigOptions  = function(ckEditorToolbarOptions, tokenCallback, textarea = null) {
    const defaultOptions = ['undo', 'redo', '|', 'bold', 'italic', 'underline', 'heading', 'fontfamily', 'fontsize', 'fontColor', 'fontBackgroundColor', 'alignment', 'numberedList', 'bulletedList', 'blockQuote', 'removeFormat', 'link', 'ckfinder', 'mediaEmbed', 'insertTable', 'sourceEditing'];
    const ckEditorToolbar = typeof ckEditorToolbarOptions != "undefined" && ckEditorToolbarOptions.length > 0 ? ckEditorToolbarOptions : defaultOptions;
    const ckEditorColors = [
        { color: '#000000', label: 'Black' },
        { color: '#4d4d4d', label: 'Dim grey' },
        { color: '#999999', label: 'Grey' },
        { color: '#e6e6e6', label: 'Light grey' },
        { color: '#ffffff', label: 'White', hasBorder: true },
        { color: '#e64c4c', label: 'Red' },
        { color: '#e6994c', label: 'Orange' },
        { color: '#e6e64c', label: 'Yellow' },
        { color: '#99e64c', label: 'Light green' },
        { color: '#4ce64c', label: 'Green' },
        { color: '#4ce699', label: 'Aquamarine' },
        { color: '#4ce6e6', label: 'Turquoise' },
        { color: '#4c99e6', label: 'Light blue' },
        { color: '#4c4ce6', label: 'Blue' },
        { color: '#994ce6', label: 'Purple' }
    ];
    const allowFullHtml = textarea && typeof textarea.attr('allow-full-html') !== 'undefined';
    const ckEditorOption = {
        toolbar: {
            items: ckEditorToolbar,
            shouldNotGroupWhenFull: true
        },
        fontFamily: {
            options: MailVotech.getCKEditorFonts(mailvotechEditorFonts),
            shouldNotGroupWhenFull: true
        },
        fontSize: {
            options: [8, 9, 10, 11, 12, 14, 18, 24, 30, 36, 48, 72],
            supportAllValues : true
        },
        fontColor: {
            // Use 'hex' format for output instead of 'hsl' as it causes problems in emails
            colorPicker: {
                format: 'hex'
            },
            colors: ckEditorColors
        },
        fontBackgroundColor: {
            // Use 'hex' format for output instead of 'hsl' as it causes problems in emails
            colorPicker: {
                format: 'hex'
            },
            colors: ckEditorColors
        },
        link: {
            allowCreatingEmptyLinks: true, // allow creation of empty links, as it was before the 14.x update of cke5
            decorators: {
                // based on: https://ckeditor.com/docs/ckeditor5/latest/features/link.html#adding-target-and-rel-attributes-to-external-links
                openInNewTab: {
                    mode: 'manual',
                    label: 'Open in a new tab',
                    attributes: {
                        target: '_blank',
                        rel: 'noopener noreferrer'
                    }
                }
            },
            // You can use `s?` suffix like below to allow both `http` and `https` protocols at the same time.
            allowedProtocols: [ 'https?', 'tel', 'sms', 'sftp', 'smb', 'slack' ]
        },
        htmlSupport: {
            fullPage: {
                allowRenderStylesFromHead: allowFullHtml
            },
            allow: allowFullHtml ? [
                {
                    // Allow all HTML elements
                    name: /.*/,
                    attributes: true,
                    classes: true,
                    styles: true
                }
            ] : [
                {
                    name: /^(a|span)$/,
                    attributes: true,
                    classes: true,
                    styles: true
                }
            ],
        },
    };


    mQuery.extend(ckEditorOption, {
        autosave: {
            save( editor ) {
                editor.updateSourceElement();
            }
        }
    });

    if (ckEditorToolbar.indexOf('ckfinder') > -1)
    {
        mQuery.extend(ckEditorOption, {
            ckfinder: {
                uploadUrl: MailVotech.imageUploadURL+'?editor=ckeditor'
            },
            image: {
                toolbar: [
                    'imageResize',
                    'imageTextAlternative',
                    '|',
                    'imageStyle:inline',
                    'imageStyle:block',
                    'imageStyle:side',
                    '|',
                    'linkImage'
                ],
            }
        });
    } else {
        mQuery.extend(ckEditorOption, {
            removePlugins: ["Image", "ImageCaption", "ImageInsert", "ImageResize", "ImageStyle", "ImageToolbar", "AutoImage", "ImageInline"]
        });
    }

    if (ckEditorToolbar.indexOf('insertTable') > -1)
    {
        mQuery.extend(ckEditorOption, {
            table: {
                contentToolbar: [
                    'tableColumn',
                    'tableRow',
                    'mergeTableCells'
                ]
            }
        });
    }

    if (ckEditorToolbar.indexOf('TokenPlugin') > -1)
    {
        const tokens = MailVotech.getTokensForPlugIn(tokenCallback);
        mQuery.extend(ckEditorOption, {
            extraPlugins: [MailVotech.MentionLinks],
            dynamicTokenLabel: 'Insert token',
            dynamicToken: tokens,
            mention: {
                feeds: [
                    {
                        marker: '{',
                        feed: MailVotech.getFeedItems,
                        itemRenderer: MailVotech.customItemRenderer
                    }
                ]
            }
        });
    }
    return ckEditorOption;
}

MailVotech.InitCkEditor  = function(textarea, options) {
    ClassicEditor
        .create( textarea[0], options)
        .then( editor => {
            ckEditors.set( textarea[0], editor);
            if (textarea.hasClass('editor-advanced') || textarea.hasClass('editor-basic-fullpage')) {
                editor.editing.view.document.on('change:isFocused', (evt, data, isFocused) => {
                    MailVotech.showChangeThemeWarning = isFocused;
                });
            }

            const ckf = editor.commands.get('ckfinder');
            if (ckf) {
                ckf.execute = () => {
                    const width = screen.width * 0.7;
                    const height = screen.height * 0.7;
                    const iLeft = (screen.width - width) / 2 ;
                    const iTop = (screen.height - height) / 2 ;
                    let sOptions = "toolbar=no,status=no,resizable=yes,dependent=yes" ;
                    sOptions += ",width=" + width ;
                    sOptions += ",height=" + height ;
                    sOptions += ",left=" + iLeft ;
                    sOptions += ",top=" + iTop ;
                    const elPopup = window.open( MailVotech.elfinderURL+ '?editor=ckeditor', "BrowseWindow", sOptions ) ;
                    elPopup.addEventListener('load', function(){
                        elPopup.editor = editor;
                    });
                };
            }
        } )
        .catch( err => {
            console.error( err.stack );
        } );
}

window.document.ckEditorInsertImages = function(editor, imageUrl) {
    const ntf = editor.plugins.get('Notification'),
        i18 = editor.locale.t,
        imgCmd = editor.commands.get('imageUpload');

    if (!imgCmd.isEnabled) {
        ntf.showWarning(i18('Could not insert image at the current position.'), {
            title: i18('Inserting image failed'),
            namespace: 'ckfinder'
        });
        return;
    }
    editor.execute('imageInsert', { source: imageUrl });
}
