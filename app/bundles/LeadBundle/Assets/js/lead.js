//LeadBundle
MailVotech.companyOnLoad = function (container, response) {

    if (mQuery(container + ' #list-search').length) {
        MailVotech.activateSearchAutocomplete('list-search', 'lead.company');
    }
    MailVotech.loadAndProcessPageContent('#company_contact_engagement');
    MailVotech.loadAndProcessPageContent('#contacts-table');
}
MailVotech.leadOnLoad = function (container, response) {
    MailVotech.addKeyboardShortcut('a', 'Quick add a New Contact', function(e) {
        if(mQuery('a.quickadd').length) {
            mQuery('a.quickadd').click();
        } else if (mQuery('a.btn-leadnote-add').length) {
            mQuery('a.btn-leadnote-add').click();
        }
    }, 'contact pages');

    MailVotech.addKeyboardShortcut('v', 'Toggle View', function(e) {
        if (mQuery('.shuffle-grid').length) {
            // If the card view is currently active, switch to table view
            mQuery('#table-view').click();
        } else {
            // If the table view is currently active, switch to card view
            mQuery('#card-view').click();
        }
    }, 'contact pages');

    //Prevent single combo keys from initiating within lead note
    Mousetrap.stopCallback = function(e, element, combo) {
        if (element.id == 'leadnote_text' && combo != 'mod+enter') {
            return true;
        }

        // if the element has the class "mousetrap" then no need to stop
        if ((' ' + element.className + ' ').indexOf(' mousetrap ') > -1) {
            return false;
        }

        // stop for input, select, and textarea
        return element.tagName == 'INPUT' || element.tagName == 'SELECT' || element.tagName == 'TEXTAREA' || (element.contentEditable && element.contentEditable == 'true');
    };

    // Timeline filters
    var timelineForm = mQuery(container + ' #timeline-filters');
    if (timelineForm.length) {
        timelineForm.on('change', function() {
            timelineForm.submit();
        }).on('keyup', function() {
            timelineForm.delay(200).submit();
        }).on('submit', function(e) {
            e.preventDefault();
            MailVotech.refreshLeadTimeline(timelineForm);
        });

        var toggleTimelineDetails = function (el) {
            var activateDetailsState = mQuery(el).hasClass('active');

            if (activateDetailsState) {
                mQuery('#timeline-details-'+detailsId).addClass('hide');
                mQuery(el).removeClass('active');
            } else {
                mQuery('#timeline-details-'+detailsId).removeClass('hide');
                mQuery(el).addClass('active');
            }
        };

        MailVotech.leadTimelineOnLoad(container, response);
        MailVotech.leadAuditlogOnLoad(container, response);
    }

    // Auditlog filters
    var auditlogForm = mQuery(container + ' #auditlog-filters');
    if (auditlogForm.length) {
        auditlogForm.on('change', function() {
            auditlogForm.submit();
        }).on('keyup', function() {
            auditlogForm.delay(200).submit();
        }).on('submit', function(e) {
            e.preventDefault();
            MailVotech.refreshLeadAuditLog(auditlogForm);
        });
    }

    //Note type filters
    var noteForm = mQuery(container + ' #note-filters');
    if (noteForm.length) {
        noteForm.on('change', function() {
            noteForm.submit();
        }).on('keyup', function() {
            noteForm.delay(200).submit();
        }).on('submit', function(e) {
            e.preventDefault();
            MailVotech.refreshLeadNotes(noteForm);
        });
    }

    if (mQuery(container + ' #list-search').length) {
        MailVotech.activateSearchAutocomplete('list-search', 'lead.lead');
    }

    if (mQuery(container + ' #notes-container').length) {
        MailVotech.activateSearchAutocomplete('NoteFilter', 'lead.note');
    }

    if (mQuery('#lead_preferred_profile_image').length) {
        mQuery('#lead_preferred_profile_image').on('change', function() {
            if (mQuery(this).val() == 'custom') {
                mQuery('#customAvatarContainer').slideDown('fast');
            } else {
                mQuery('#customAvatarContainer').slideUp('fast');
            }
        })
    }

    if (mQuery('.lead-avatar-panel').length) {
        mQuery('.lead-avatar-panel .avatar-collapser a.arrow').on('click', function() {
            setTimeout(function() {
                var status = (mQuery('#lead-avatar-block').hasClass('in') ? 'expanded' : 'collapsed');
                document.cookie = 'mailvotech_lead_avatar_panel=' + status + '; path=/; max-age=' + (30 * 24 * 60 * 60) + '; SameSite=Strict';
            }, 500);
        });
    }

    if (mQuery('#anonymousLeadButton').length) {
        var searchValue = mQuery('#list-search').typeahead('val').toLowerCase();
        var string      = mQuery('#anonymousLeadButton').data('anonymous').toLowerCase();

        if (searchValue.indexOf(string) >= 0 && searchValue.indexOf('!' + string) == -1) {
            mQuery('#anonymousLeadButton').addClass('btn-primary');
        } else {
            mQuery('#anonymousLeadButton').removeClass('btn-primary');
        }
    }

    var leadMap = [];

    mQuery(document).on('shown.bs.tab', 'a#load-lead-map',  () => {
        leadMap = MailVotech.initMap('#place-container', 'markers');
    });

    mQuery('a[data-toggle="tab"]').not('a#load-lead-map').on('shown.bs.tab', function (e) {
        if (leadMap.length) {
            leadMap.destroyMap();
            leadMap = undefined;
        }
    });

    MailVotech.initUniqueIdentifierFields();

    if (mQuery(container + ' .panel-companies').length) {
        mQuery(container + ' .panel-companies .ri-check-line').tooltip({html: true});
    }

    // Adding behavior to be able to create new tags by pressing the `Enter` or `Escape` key
    // when the search field is active (ie: the tag name we are typing is a substring of an existing tag)
    mQuery('#lead_tags_chosen input').keyup(function(el) {
        const newTag = mQuery('#lead_tags_chosen input').val().trim();
        if ((el.key === "Escape" || el.key === "Enter") && newTag !== '') {
            const selectElement = mQuery('#lead_tags').get();
            const selectedValues = mQuery('#lead_tags').val() || [];
            const payload = [...selectedValues, newTag];

            MailVotech.activateLabelLoadingIndicator(mQuery(selectElement).attr('id'));
            MailVotech.ajaxActionRequest('lead:addLeadTags', {tags: JSON.stringify(payload)}, function(response) {
                if (response.tags) {
                    mQuery('#' + mQuery(selectElement).attr('id')).html(response.tags);
                    mQuery('#' + mQuery(selectElement).attr('id')).trigger('chosen:updated');
                }

                MailVotech.removeLabelLoadingIndicator();
            });
        }
    });

    MailVotech.lazyLoadContactStatsOnLeadLoad();
};

MailVotech.leadTimelineOnLoad = function (container, response) {
    mQuery("#contact-timeline a[data-activate-details='all']").on('click', function() {
        var $icon = mQuery(this).find('span').first();
        if ($icon.hasClass('ri-arrow-down-s-line')) {
            mQuery("#contact-timeline a[data-activate-details!='all']").each(function () {
                var detailsId = mQuery(this).data('activate-details');
                if (detailsId && mQuery('#timeline-details-' + detailsId).length) {
                    mQuery('#timeline-details-' + detailsId).removeClass('hide');
                    mQuery(this).addClass('active');
                }
            });
            $icon.removeClass('ri-arrow-down-s-line').addClass('ri-arrow-up-s-line');
        } else {
            mQuery("#contact-timeline a[data-activate-details!='all']").each(function () {
                var detailsId = mQuery(this).data('activate-details');
                if (detailsId && mQuery('#timeline-details-' + detailsId).length) {
                    mQuery('#timeline-details-' + detailsId).addClass('hide');
                    mQuery(this).removeClass('active');
                }
            });
            $icon.removeClass('ri-arrow-up-s-line').addClass('ri-arrow-down-s-line');
        }
    });

    mQuery("#contact-timeline a[data-activate-details!='all']").on('click', function() {
        var detailsId = mQuery(this).data('activate-details');
        var $icon = mQuery(this).find('span').first();
        if (detailsId && mQuery('#timeline-details-' + detailsId).length) {
            var activateDetailsState = mQuery(this).hasClass('active');
            if (activateDetailsState) {
                mQuery('#timeline-details-' + detailsId).addClass('hide');
                mQuery(this).removeClass('active');
                $icon.removeClass('ri-arrow-up-s-line').addClass('ri-arrow-down-s-line');
            } else {
                mQuery('#timeline-details-' + detailsId).removeClass('hide');
                mQuery(this).addClass('active');
                $icon.removeClass('ri-arrow-down-s-line').addClass('ri-arrow-up-s-line');
            }
        }
    });

    if (response && typeof response.timelineCount !== 'undefined') {
        mQuery('#TimelineCount').html(response.timelineCount);
    }
};

MailVotech.leadAuditlogOnLoad = function (container, response) {
    mQuery("#contact-auditlog a[data-activate-details='all']").on('click', function() {
        var $icon = mQuery(this).find('span').first();
        if ($icon.hasClass('ri-arrow-down-s-line')) {
            mQuery("#contact-auditlog a[data-activate-details!='all']").each(function () {
                var detailsId = mQuery(this).data('activate-details');
                if (detailsId && mQuery('#auditlog-details-' + detailsId).length) {
                    mQuery('#auditlog-details-' + detailsId).removeClass('hide');
                    mQuery(this).addClass('active');
                }
            });
            $icon.removeClass('ri-arrow-down-s-line').addClass('ri-arrow-up-s-line');
        } else {
            mQuery("#contact-auditlog a[data-activate-details!='all']").each(function () {
                var detailsId = mQuery(this).data('activate-details');
                if (detailsId && mQuery('#auditlog-details-' + detailsId).length) {
                    mQuery('#auditlog-details-' + detailsId).addClass('hide');
                    mQuery(this).removeClass('active');
                }
            });
            $icon.removeClass('ri-arrow-up-s-line').addClass('ri-arrow-down-s-line');
        }
    });

    mQuery("#contact-auditlog a[data-activate-details!='all']").on('click', function() {
        var detailsId = mQuery(this).data('activate-details');
        var $icon = mQuery(this).find('span').first();
        if (detailsId && mQuery('#auditlog-details-' + detailsId).length) {
            var activateDetailsState = mQuery(this).hasClass('active');
            if (activateDetailsState) {
                mQuery('#auditlog-details-' + detailsId).addClass('hide');
                mQuery(this).removeClass('active');
                $icon.removeClass('ri-arrow-up-s-line').addClass('ri-arrow-down-s-line');
            } else {
                mQuery('#auditlog-details-' + detailsId).removeClass('hide');
                mQuery(this).addClass('active');
                $icon.removeClass('ri-arrow-down-s-line').addClass('ri-arrow-up-s-line');
            }
        }
    });
};

MailVotech.leadOnUnload = function(id) {
    if (typeof MailVotechVars.moderatedIntervals['leadListLiveUpdate'] != 'undefined') {
        MailVotech.clearModeratedInterval('leadListLiveUpdate');
    }

    if (typeof MailVotech.mapObjects !== 'undefined') {
        delete MailVotech.mapObjects;
    }
};

MailVotech.getLeadId = function() {
    return mQuery('input#leadId').val();
}

MailVotech.leadlistOnLoad = function(container, response) {
    const segmentCountElem = mQuery('span.col-count');

    if (segmentCountElem.length) {
        segmentCountElem.each(function() {
            const elem = mQuery(this);
            const id = elem.attr('data-id');

            MailVotech.ajaxActionRequest(
                'lead:getLeadCount',
                {id: id},
                function (response) {
                    elem.className = response.className;
                    elem.children('a').html(response.html);
                },
                false,
                true,
                "GET"
            );
        });
    }

    mQuery('#campaign-share-tab').hover(function () {
        if (MailVotech.shareTableLoaded != true) {
            MailVotech.loadAjaxColumn('campaign-share-stat', 'lead:getCampaignShareStats', 'afterStatsLoad');
            MailVotech.shareTableLoaded = true;
        }
    })

    MailVotech.afterStatsLoad = function () {
        MailVotech.sortTableByColumn('#campaign-share-table', '.campaign-share-stat', true)
    }


    if (mQuery(container + ' #list-search').length) {
        MailVotech.activateSearchAutocomplete('list-search', 'lead.list');
    }

    var prefix = 'leadlist';
    var parent = mQuery('.dynamic-content-filter, .dwc-filter');
    if (parent.length) {
        prefix = parent.attr('id');
    }

    if (mQuery('#' + prefix + '_filters').length) {
        mQuery('#available_segment_filters').on('change', function() {
            if (mQuery(this).val()) {
                MailVotech.addLeadListFilter(mQuery(this).val(),mQuery('option:selected',this).data('field-object'));
                mQuery(this).val('');
                mQuery(this).trigger('chosen:updated');
            }
        });

        mQuery('#' + prefix + '_filters .segment-filter').each( function (index, filter) {
            MailVotech.segmentFilter().attachEvents(mQuery(filter));
        });

        var bodyOverflow = {};
        mQuery('#' + prefix + '_filters').sortable({
            items: '.filter--row',
            helper: function(e, ui) {
                ui.children().each(function() {
                    if (mQuery(this).is(":visible")) {
                        mQuery(this).width(mQuery(this).width());
                    }
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
            stop: function(e, ui) {
                // Restore original overflow
                mQuery('body').css(bodyOverflow);

                MailVotech.reorderSegmentFilters();
            }
        });
    }

    // segment contact filters
    var segmentContactForm = mQuery('#segment-contact-filters');

    if (segmentContactForm.length) {
        segmentContactForm.on('change', function() {
            segmentContactForm.submit();
        }).on('keyup', function() {
            segmentContactForm.delay(200).submit();
        }).on('submit', function(e) {
            e.preventDefault();
            MailVotech.refreshSegmentContacts(segmentContactForm);
        });
    }

    jQuery(document).ajaxComplete(function(){
        MailVotech.ajaxifyForm('daterange');
    });

    MailVotech.attachJsUiOnFilterForms();
};

/**
 * Trigger event so plugins could attach other JS magic to the form.
 */
MailVotech.triggerOnPropertiesFormLoadedEvent = function(selector, filterValue) {
    mQuery('#leadlist_filters').trigger('filter.properties.form.loaded', [selector, filterValue]);
};

MailVotech.attachJsUiOnFilterForms = function() {
    mQuery('#leadlist_filters').on('filter.properties.form.loaded', function(event, selector, filterValue) {
        MailVotech.activateChosenSelect(selector + '_properties select');
        var fieldType = mQuery(selector + '_type').val();
        var fieldAlias = mQuery(selector + '_field').val();
        var filterFieldEl = mQuery(selector + '_properties_filter');

        if (filterValue) {
            filterFieldEl.val(filterValue);
            if (filterFieldEl.is('select')) {
                filterFieldEl.trigger('chosen:updated');
            }
        }

        if (fieldType === 'lookup') {
            MailVotech.activateLookupTypeahead(filterFieldEl.parent());
        } else if (fieldType === 'datetime') {
            filterFieldEl.datetimepicker({
                format: 'Y-m-d H:i',
                lazyInit: true,
                validateOnBlur: false,
                allowBlank: true,
                scrollMonth: false,
                scrollInput: false
            });
        } else if (fieldType === 'date') {
            filterFieldEl.datetimepicker({
                timepicker: false,
                format: 'Y-m-d',
                lazyInit: true,
                validateOnBlur: false,
                allowBlank: true,
                scrollMonth: false,
                scrollInput: false,
                closeOnDateSelect: true
            });
        } else if (fieldType === 'time') {
            filterFieldEl.datetimepicker({
                datepicker: false,
                format: 'H:i',
                lazyInit: true,
                validateOnBlur: false,
                allowBlank: true,
                scrollMonth: false,
                scrollInput: false
            });
        } else if (fieldType === 'lookup_id') {
            var displayFieldEl = mQuery(selector + '_properties_display');
            var fieldCallback = displayFieldEl.attr('data-field-callback');
            if (fieldCallback && typeof MailVotech[fieldCallback] === 'function') {
                var fieldOptions = displayFieldEl.attr('data-field-list');
                MailVotech[fieldCallback](selector.replace('#', '') + '_properties_display', fieldAlias, fieldOptions);
            }
        }
        mQuery('.chosen-search-input').on('keypress', function (event) {
            if ( event.which === 13 ) event.preventDefault();
        })
    });

    // Trigger event so plugins could attach other JS magic to the form.
    mQuery('#leadlist_filters .panel').each(function() {
        MailVotech.triggerOnPropertiesFormLoadedEvent('#' + mQuery(this).attr('id'));
    });
};

MailVotech.reorderSegmentFilters = function() {
    // Update the filter numbers sot that they are ordered correctly when processed and grouped server side
    var counter = 0;

    var prefix = 'leadlist';
    var parent = mQuery('.dynamic-content-filter, .dwc-filter');
    if (parent.length) {
        prefix = parent.attr('id');
    }

    const $filters = mQuery('#' + prefix + '_filters .filter--row');

    $filters.each(function() {
        const $filter = mQuery(this);
        $filter.attr('id',prefix + '_filters_'+counter);
        MailVotech.updateFilterPositioning($filter.find('select.glue-select').first());
        $filter.find('[id^="' + prefix + '_filters_"]').each(function() {
            const $element = mQuery(this);
            var id     = $element.attr('id');
            var name   = $element.attr('name');
            var suffix = id.split(/[_]+/).pop();

            var isProperties = id.includes("_properties_");

            if (prefix + '_filters___name___filter' === id) {
                return true;
            }

            if (name) {
                if (isProperties) {
                    const suffixIdMatch = id.match(/_properties_(.*)$/);
                    const suffixNameMatch = name.match(/\[properties\](.*)$/);
                    const suffixId = suffixIdMatch ? suffixIdMatch[1] : suffix;
                    const suffixName = suffixNameMatch ? suffixNameMatch[1] : suffix;
                    var newName = prefix + '[filters][' + counter + '][properties]' + suffixName;
                    suffix = 'properties_' + suffixId;
                } else {
                    var newName = prefix + '[filters][' + counter + '][' + suffix + ']';
                    if (name.slice(-2) === '[]') {
                        newName += '[]';
                    }
                }
                $element.attr('name', newName);
            }
            $element.attr('id', prefix + '_filters_'+counter+'_'+suffix);

            // Destroy the chosen and recreate
            if ($element.is('select') && suffix.includes("_filter")) {
                MailVotech.destroyChosen($element);
                MailVotech.activateChosenSelect($element);
            }

            MailVotech.segmentFilter().showCopyBasedOnGlue($filter);

            if ($element.is(':radio') && id.includes("_dateTypeMode_")) {
                if ($element.closest('label').hasClass('active')) {
                    $element.click();
                }
            }
        });

        $filter.find('.panel-heading').css('width', ''); // Something is setting width. Remove it.

        ++counter;
    });

    const panelClass = prefix === 'leadlist' ? '.panel-glue' : '.panel-heading';
    mQuery('#'+prefix+'_filters '+panelClass).removeClass('hide');
    const $firstPanel = $filters.first();
    $firstPanel.find(panelClass).addClass('hide');
    $firstPanel.find('.copy-filter-group').removeClass('hide');

    const $tooltips = $filters.find("*[data-toggle='tooltip']");
    $tooltips.each(function() {
        mQuery(this).tooltip({html: true, container: 'body'});
    });
};

MailVotech.convertLeadFilterInput = function(el) {
    var operatorSelect = mQuery(el);
    // Extract the filter number
    var regExp = /_filters_(\d+)_operator/;
    var matches = regExp.exec(operatorSelect.attr('id'));
    var filterNum = matches[1];
    var fieldAlias = mQuery('#leadlist_filters_'+filterNum+'_field');
    var fieldObject = mQuery('#leadlist_filters_'+filterNum+'_object');
    var filterValue = mQuery('#leadlist_filters_'+filterNum+'_properties_filter').val();
    var filterId  = '#leadlist_filters_' + filterNum + '_properties_filter';

    MailVotech.loadFilterForm(filterNum, fieldObject.val(), fieldAlias.val(), operatorSelect.val(), function(propertiesFields) {
        var selector = '#leadlist_filters_'+filterNum;
        mQuery(selector+'_properties').html(propertiesFields);

        MailVotech.ajaxifyForm('leadlist');

        MailVotech.triggerOnPropertiesFormLoadedEvent(selector, filterValue);
    });

    MailVotech.setProcessorForFilterValue(filterId, operatorSelect.val());
};

MailVotech.setFilterValuesProcessor = function () {
    mQuery('.filter-operator').each(function (index) {
        let filterId = "#" + mQuery('.filter-value').eq(index).attr('id');
        MailVotech.setProcessorForFilterValue(filterId, mQuery(this).val())
    });
};

MailVotech.setProcessorForFilterValue = function (filterId, operator) {
    let isInOperator = (operator == 'in' || operator == '!in');
    if (isInOperator && mQuery(filterId).attr('type') === 'text') {
        mQuery(filterId).on('paste', function (e) {
            let value  = e.originalEvent.clipboardData.getData('text');
            value = value.replace(/\r?\n/g, '|');
            if (value.slice(-1) === '|') {
                value = value.slice(0, -1);
            }
            mQuery(filterId).val(value);
            e.preventDefault();
        });
    } else {
        mQuery(filterId).off('paste');
    }
};

/**
 * Adds values to the lookup_id form after user selects a typeahead option.
 */
MailVotech.updateLookupListFilter = function(field, item) {
    if (item && item.id) {
        var filterField = '#'+field.replace('_display', '_filter');
        mQuery(filterField).val(item.id);
        mQuery(field).val(item.name);
    }
};

MailVotech.activateSegmentFilterTypeahead = function(displayId, filterId, fieldOptions, mQueryObject) {

    var mQueryBackup = mQuery;

    if (typeof mQueryObject === 'function') {
        mQuery = mQueryObject;
    }

    mQuery('#' + displayId).attr('data-lookup-callback', 'updateLookupListFilter');

    MailVotech.activateFieldTypeahead(displayId, filterId, [], mQuery('#' + displayId).data('action') || 'lead:fieldList');

    mQuery = mQueryBackup;
};

MailVotech.loadFilterForm = function(filterNum, fieldObject, fieldAlias, operator, resultHtml, search = null) {
    mQuery.ajax({
        showLoadingBar: true,
        url: mailvotechAjaxUrl,
        type: 'POST',
        data: {
            action: 'lead:loadSegmentFilterForm',
            fieldAlias: fieldAlias,
            fieldObject: fieldObject,
            operator: operator,
            filterNum: filterNum,
            search: search,
        },
        dataType: 'json',
        success: function (response) {
            MailVotech.stopPageLoadingBar();
            resultHtml(response.viewParameters.form);
            if (fieldAlias == 'lead_asset_download') {
                MailVotech.handleAssetDownloadSearch(filterNum, fieldObject, fieldAlias, operator, resultHtml, search);
            }
        },
        error: function (request, textStatus, errorThrown) {
            MailVotech.processAjaxError(request, textStatus, errorThrown);
        }
    });
}

MailVotech.addLeadListFilter = function (elId, elObj) {
    var filterId = '#available_' + elObj + '_' + elId;
    var filterOption = mQuery(filterId);

    // Create a new filter

    var filterNum = MailVotech.segmentFilter().getFilterCount();
    var prototypeStr = mQuery('.available-filters').data('prototype');
    var fieldType = filterOption.data('field-type');
    var fieldObject = filterOption.data('field-object');
    var label = filterOption.data('field-label');

    prototypeStr = prototypeStr.replace(/__name__/g, filterNum);
    prototypeStr = prototypeStr.replace(/__label__/g, label);

    // Convert to DOM
    prototype = mQuery(prototypeStr);

    var prefix = 'leadlist';
    var parent = mQuery(filterId).parents('.dynamic-content-filter, .dwc-filter');
    if (parent.length) {
        prefix = parent.attr('id');
    }

    var filterBase  = prefix + "[filters][" + filterNum + "]";
    var filterIdBase = prefix + "_filters_" + filterNum + "_";

    if (MailVotech.segmentFilter().getFilterCount() === 0) {
        // First filter so hide the glue footer
        prototype.find(".panel-glue").addClass('hide');
    }

    const filterTypeIcon = filterOption.data('field-icon');
    prototype.find('.object-icon').removeClass('ri-shapes-line').addClass(filterTypeIcon);

    prototype.find(".inline-spacer").append(fieldObject);

    MailVotech.segmentFilter().attachEvents(prototype);

    prototype.find("input[name='" + filterBase + "[field]']").val(elId);
    prototype.find("input[name='" + filterBase + "[type]']").val(fieldType);
    prototype.find("input[name='" + filterBase + "[object]']").val(fieldObject);
    prototype.appendTo('#' + prefix + '_filters');

    var operators = filterOption.data('field-operators');
    mQuery('#' + filterIdBase + 'operator').html('');
    mQuery.each(operators, function (label, value) {
        var newOption = mQuery('<option/>').val(value).text(label);
        newOption.appendTo(mQuery('#' + filterIdBase + 'operator'));
    });

    // Convert based on first option in list
    MailVotech.convertLeadFilterInput('#' + filterIdBase + 'operator');

    // Reposition if applicable
    MailVotech.updateFilterPositioning(mQuery('#' + filterIdBase + 'glue'));

    MailVotech.segmentFilter().showCopyBasedOnGlue(prototype);
};

MailVotech.segmentFilter = function() {

    const attachEvents = function($filter) {
        _attachCopyEvents($filter);
        _attachRemoveEvents($filter);
        _attachGlueEvents($filter);
    };

    const getFilterCount = function() {
        return mQuery('.selected-filters').children('.filter--row').length;
    };

    const showCopyBasedOnGlue = function($filter) {
        const $glue = $filter.find('select.glue-select');
        const $copyButton = $filter.find('.copy-filter-group');
        if ($glue.val() === 'and' && !_isFirstFilter($filter)) {
            $copyButton.addClass('hide');
        } else {
            $copyButton.removeClass('hide');
        }
    };

    const _attachGlueEvents = function($filter) {
        showCopyBasedOnGlue($filter);
        $filter.find('select.glue-select').on('change', function () {
            showCopyBasedOnGlue($filter);
        });
    };

    const _isFirstFilter = function($filter) {
        return $filter.prev().length === 0;
    }

    const _attachRemoveEvents = function($filter) {
        $filter.find('a.remove-selected').each(function (index, el) {
            mQuery(el).on('click', function () {
                $filter.animate(
                    {'opacity': 0},
                    'fast',
                    function () {
                        // Remove existing tooltip
                        mQuery('*[role="tooltip"]').tooltip('destroy');
                        mQuery(this).remove();
                        MailVotech.reorderSegmentFilters();
                    }
                );
            });
        });
    };

    const _attachCopyEvents = function($filter) {
        $filter.find('.copy-filter-group').on('click', function(event) {
            event.preventDefault();
            $copyButton = mQuery(this);
            $filter = $copyButton.closest('.segment-filter');
            _cloneFilter($filter);
            let groupEnded = false;
            $filter.nextAll().each(function(i, element) {
                $nextFilter = mQuery(element);
                if (!$nextFilter.hasClass('in-group')) {
                    groupEnded = true;
                }
                if (groupEnded) {
                    return;
                }
                _cloneFilter($nextFilter);
            });
        });
    };

    /**
     * Set selected param for options otherwise they won't be cloned as selected.
     */
    const _setSelectedOptions = function($filter) {
        $filter.find('select option').each(function() {
            const $option = mQuery(this);
            $option.attr('selected',  $option.is(':selected') ? 'selected' : null);
        });
    }

    const _cloneFilter = function($origin) {
        $origin.find('.properties-form select').chosen('destroy');
        _setSelectedOptions($origin);
        const $clone = $origin.clone(false);

        if (!$origin.hasClass('in-group')) {
            const $glueWrapper = $clone.find('.panel-glue');
            $glueWrapper.find('select').val('or');
            $glueWrapper.removeClass('hide');
        }

        // Hide the "When" text in the cloned filter
        $clone.find('.filter--condition-when').addClass('hide');

        const $filters = $origin.closest('.selected-filters');

        $filters.append($clone);
        MailVotech.reorderSegmentFilters();
        MailVotech.triggerOnPropertiesFormLoadedEvent('#' + $clone.attr('id'));
        attachEvents($clone);
    }

    return {
        attachEvents,
        getFilterCount,
        showCopyBasedOnGlue,
    };
}



MailVotech.leadfieldOnLoad = function (container) {
    if (mQuery(container + ' .leadfield-list').length) {
        var bodyOverflow = {};
        mQuery(container + ' .leadfield-list tbody').sortable({
            handle: '.ri-draggable',
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
            scroll: false,
            axis: 'y',
            containment: container + ' .leadfield-list',
            stop: function(e, ui) {
                // Restore original overflow
                mQuery('body').css(bodyOverflow);

                // Get the page and limit
                mQuery.ajax({
                    type: "POST",
                    url: mailvotechAjaxUrl + "?action=lead:reorder&limit=" + mQuery('.pagination-limit').val() + '&page=' + mQuery('.pagination li.active a span').first().text(),
                    data: mQuery(container + ' .leadfield-list tbody').sortable("serialize")
                });
            }
        });
    }

    if (mQuery(container + ' form[name="leadfield"]').length) {
        MailVotech.updateLeadFieldProperties(mQuery('#leadfield_type').val(), true);
    }

};

MailVotech.updateLeadFieldProperties = function(selectedVal, onload) {
    let isMultiselect = selectedVal === 'multiselect' ? true : false;
    if (selectedVal === 'multiselect') {
        // Use select
        selectedVal = 'select';
    }

    mQuery('#leadfield_properties [data-toggle="tooltip"]').tooltip('destroy');

    if (mQuery('#field-templates .' + selectedVal).length) {
        mQuery('#leadfield_properties').html(
            mQuery('#field-templates .' + selectedVal).html()
                .replace(/leadfield_properties_template/g, 'leadfield_properties')
        );
        mQuery("#leadfield_properties *[data-toggle='sortablelist']").each(function (index) {
            var sortableList = mQuery(this);
            MailVotech.activateSortable(this);
            // Using an interval so removing, adding, updating and reordering are accounted for
            var contactFieldListOptions = mQuery('#leadfield_properties').find('input').map(function() {
                return mQuery(this).val();
            }).get().join();
            var updateDefaultValuesetInterval = setInterval(function() {
                var evalListOptions = mQuery('#leadfield_properties').find('input').map(function() {
                    return mQuery(this).val();
                }).get().join();
                if (mQuery('#leadfield_properties_itemcount').length) {
                    if (contactFieldListOptions != evalListOptions) {
                        contactFieldListOptions = evalListOptions;
                        var selected = mQuery('#leadfield_defaultValue').val();
                        mQuery('#leadfield_defaultValue').html('<option value=""></option>');
                        var labels = mQuery('#leadfield_properties').find('input.sortable-label');
                        if (labels.length) {
                            labels.each(function () {
                                // label/value pairs
                                var label = mQuery(this).val();
                                var val = mQuery(this).closest('.row').find('input.sortable-value').first().val();
                                mQuery('<option value="' + val + '">' + label + '</option>').appendTo(mQuery('#leadfield_defaultValue'));
                            });
                        } else {
                            mQuery('#leadfield_properties .list-sortable').find('input').each(function () {
                                var val = mQuery(this).val();
                                mQuery('<option value="' + val + '">' + val + '</option>').appendTo(mQuery('#leadfield_defaultValue'));
                            });
                        }

                        mQuery('#leadfield_defaultValue').val(selected);
                        mQuery('#leadfield_defaultValue').trigger('chosen:updated');
                    }
                } else {
                    clearInterval(updateDefaultValuesetInterval);
                    delete contactFieldListOptions;
                }
            }, 500);
        });

        mQuery('#leadfield_properties [data-toggle="tooltip"]').tooltip();
    } else if (!mQuery('#leadfield_properties .' + selectedVal).length) {
        mQuery('#leadfield_properties').html('');
    }

    if (selectedVal == 'time') {
        mQuery('#leadfield_isListable').closest('.row').addClass('hide');
    } else {
        mQuery('#leadfield_isListable').closest('.row').removeClass('hide');
    }

    // Switch default field if applicable
    var defaultValueField = mQuery('#leadfield_defaultValue');
    if (defaultValueField.hasClass('calendar-activated')) {
        defaultValueField.datetimepicker('destroy').removeClass('calendar-activated');
    } else if (mQuery('#leadfield_defaultValue_chosen').length) {
        MailVotech.destroyChosen(defaultValueField);
    }

    var defaultFieldType = mQuery('input[name="leadfield[defaultValue]"]').attr('type');
    var tempType = selectedVal;
    var html = '';
    var isSelect = false;
    var defaultVal = defaultValueField.val();
    switch (selectedVal) {
        case 'boolean':
            if (defaultFieldType != 'radio') {
                // Convert to a boolean type
                html = '<div id="leadfield_default_template_boolean">' + mQuery('#field-templates .default_template_boolean').html() + '</div>';
            }
            break;
        case 'country':
        case 'region':
        case 'locale':
        case 'timezone':
            html = mQuery('#field-templates .default_template_' + selectedVal).html();
            isSelect = true;
            break;
        case 'select':
        case 'lookup':
            html = mQuery('#field-templates .default_template_select').html();
            tempType = 'select';
            isSelect = true;
            break;
        case 'textarea':
            html = mQuery('#field-templates .default_template_textarea').html();
            break;
        default:
            html = mQuery('#field-templates .default_template_text').html();
            tempType = 'text';

            if (html != undefined && (selectedVal == 'number' || selectedVal == 'tel' || selectedVal == 'url' || selectedVal == 'email')) {
                var replace = 'type="text"';
                var regex = new RegExp(replace, "g");
                html = html.replace(regex, 'type="' + selectedVal + '"');
            }

            break;
    }

    if (html && !onload) {
        var replace = 'default_template_' + tempType;
        var regex = new RegExp(replace, "g");
        html = html.replace(regex, 'defaultValue')
        defaultValueField.replaceWith(mQuery(html));
        mQuery('#leadfield_defaultValue').val(defaultVal);
        if (isMultiselect) {
            mQuery('#leadfield_defaultValue').attr('multiple', 'multiple');
            mQuery('#leadfield_defaultValue').attr('name', mQuery('#leadfield_defaultValue').attr('name')+'[]');
        }
    }

    if (selectedVal === 'datetime' || selectedVal === 'date' || selectedVal === 'time') {
        MailVotech.activateDateTimeInputs('#leadfield_defaultValue', selectedVal);
    } else if (isSelect) {
       MailVotech.activateChosenSelect('#leadfield_defaultValue');
    }
};

MailVotech.updateLeadFieldBooleanLabels = function(el, label) {
    mQuery('#leadfield_defaultValue_' + label).parent().find('span').text(
        mQuery(el).val()
    );
};

MailVotech.updateLeadFieldOrderChoiceList = function () {
    formData = {
        'object': mQuery('#leadfield_object').val(),
        'group': mQuery('#leadfield_group').val()
    };
    MailVotech.ajaxActionRequest('lead:updateLeadFieldOrderChoiceList', formData, function(response) {
        if (response) {
            mQuery('#leadfield_order_container').html(response);
            MailVotech.activateChosenSelect('#leadfield_order');
            mQuery('label[for=leadfield_order]').tooltip({html: true});
        }
    });
}

MailVotech.refreshLeadSocialProfile = function(network, leadId, event) {
    var query = "action=lead:updateSocialProfile&network=" + network + "&lead=" + leadId;
    mQuery.ajax({
        showLoadingBar: true,
        url: mailvotechAjaxUrl,
        type: "POST",
        data: query,
        dataType: "json",
        success: function (response) {
            if (response.success) {
                if (response.completeProfile) {
                    mQuery('#social-container').html(response.completeProfile);
                    mQuery('#SocialCount').html(response.socialCount);
                } else {
                    //loop through each network
                    mQuery.each(response.profiles, function (index, value) {
                        if (mQuery('#' + index + 'CompleteProfile').length) {
                            mQuery('#' + index + 'CompleteProfile').html(value.newContent);
                        }
                    });
                }
            }
            MailVotech.stopPageLoadingBar();
            MailVotech.stopIconSpinPostEvent();
        },
        error: function (request, textStatus, errorThrown) {
            MailVotech.processAjaxError(request, textStatus, errorThrown);
        }
    });

    MailVotech.setFilterValuesProcessor();
};

MailVotech.clearLeadSocialProfile = function(network, leadId, event) {
    MailVotech.startIconSpinOnEvent(event);
    var query = "action=lead:clearSocialProfile&network=" + network + "&lead=" + leadId;
    mQuery.ajax({
        url: mailvotechAjaxUrl,
        type: "POST",
        data: query,
        dataType: "json",
        success: function (response) {
            if (response.success) {
                //activate the click to remove the panel
                mQuery('.' + network + '-panelremove').click();
                if (response.completeProfile) {
                    mQuery('#social-container').html(response.completeProfile);
                }
                mQuery('#SocialCount').html(response.socialCount);
            }

            MailVotech.stopIconSpinPostEvent();
        },
        error: function (request, textStatus, errorThrown) {
            MailVotech.processAjaxError(request, textStatus, errorThrown);
            MailVotech.stopIconSpinPostEvent();
        }
    });
};

MailVotech.refreshLeadAuditLog = function(form) {
    MailVotech.postForm(mQuery(form), function (response) {
        response.target = '#auditlog-table';
        mQuery('#AuditLogCount').html(response.auditLogCount);
        MailVotech.processPageContent(response);
    });
};

MailVotech.refreshLeadTimeline = function(form) {
    MailVotech.postForm(mQuery(form), function (response) {
        response.target = '#timeline-table';
        mQuery('#TimelineCount').html(response.timelineCount);
        MailVotech.processPageContent(response);
    });
};

MailVotech.refreshLeadNotes = function(form) {
    MailVotech.postForm(mQuery(form), function (response) {
        response.target = '#NoteList';
        mQuery('#NoteCount').html(response.noteCount);
        MailVotech.processPageContent(response);
    });
};

MailVotech.refreshSegmentContacts = function(form) {
    MailVotech.postForm(mQuery(form), function (response) {
        response.target = '#contacts-container';
        MailVotech.processPageContent(response);
    });
};

MailVotech.toggleLeadList = function(toggleId, leadId, listId) {
    var action = mQuery('#' + toggleId).hasClass('ri-toggle-fill') ? 'remove' : 'add';
    var query = "action=lead:toggleLeadList&leadId=" + leadId + "&listId=" + listId + "&listAction=" + action;

    MailVotech.toggleLeadSwitch(toggleId, query, action);
};

MailVotech.togglePreferredChannel = function(channel) {
    if (channel === 'all') {
        var channelsForm = mQuery('form[name="contact_channels"]');
        var status = channelsForm.find('#contact_channels_subscribed_channels_0:checked').length;
        channelsForm.find('tbody input:checkbox').each(function() {
            if (this.checked != status) {
                this.checked = status;
                MailVotech.setPreferredChannel(this.value);
            }
        });
    } else {
        MailVotech.setPreferredChannel(channel);
    }
};

MailVotech.setPreferredChannel = function(channel) {
    mQuery( '#frequency_' + channel ).slideToggle();
    mQuery( '#frequency_' + channel ).removeClass('hide');
    if (mQuery('#' + channel)[0].checked) {
        mQuery('#is-contactable-' + channel).removeClass('text-secondary');
        mQuery('#lead_contact_frequency_rules_frequency_number_' + channel).prop("disabled" , false).trigger("chosen:updated");
        mQuery('#preferred_' + channel).prop("disabled" , false);
        mQuery('#lead_contact_frequency_rules_frequency_time_' + channel).prop("disabled" , false).trigger("chosen:updated");
        mQuery('#lead_contact_frequency_rules_contact_pause_start_date_' + channel).prop("disabled" , false);
        mQuery('#lead_contact_frequency_rules_contact_pause_end_date_' + channel).prop("disabled" , false);
    } else {
        mQuery('#is-contactable-' + channel).addClass('text-secondary');
        mQuery('#lead_contact_frequency_rules_frequency_number_' + channel).prop("disabled" , true).trigger("chosen:updated");
        mQuery('#preferred_' + channel).prop("disabled" , true);
        mQuery('#lead_contact_frequency_rules_frequency_time_' + channel).prop("disabled" , true).trigger("chosen:updated");
        mQuery('#lead_contact_frequency_rules_contact_pause_start_date_' + channel).prop("disabled" , true);
        mQuery('#lead_contact_frequency_rules_contact_pause_end_date_' + channel).prop("disabled" , true);
    }
};

MailVotech.toggleCompanyLead = function(toggleId, leadId, companyId) {
    var action = mQuery('#' + toggleId).hasClass('ri-toggle-fill') ? 'remove' : 'add';
    var query = "action=lead:toggleCompanyLead&leadId=" + leadId + "&companyId=" + companyId + "&companyAction=" + action;
    MailVotech.toggleLeadSwitch(toggleId, query, action);
};

MailVotech.toggleLeadCampaign = function(toggleId, leadId, campaignId) {
    var action = mQuery('#' + toggleId).hasClass('ri-toggle-fill') ? 'remove' : 'add';
    var query  = "action=lead:toggleLeadCampaign&leadId=" + leadId + "&campaignId=" + campaignId + "&campaignAction=" + action;

    MailVotech.toggleLeadSwitch(toggleId, query, action);
};

MailVotech.toggleLeadSwitch = function(toggleId, query, action) {
    var toggleOn  = 'ri-toggle-fill text-success';
    var toggleOff = 'ri-toggle-line text-danger';
    var spinClass = 'ri-spin ri-loader-3-line ';

    if (action == 'remove') {
        //switch it on
        mQuery('#' + toggleId).removeClass(toggleOn).addClass(spinClass + 'text-danger');
    } else {
        mQuery('#' + toggleId).removeClass(toggleOff).addClass(spinClass + 'text-success');
    }

    mQuery.ajax({
        url: mailvotechAjaxUrl,
        type: "POST",
        data: query,
        dataType: "json",
        success: function (response) {
            mQuery('#' + toggleId).removeClass(spinClass);
            if (!response.success) {
                //return the icon back
                if (action == 'remove') {
                    //switch it on
                    mQuery('#' + toggleId).removeClass(toggleOff).addClass(toggleOn);
                } else {
                    mQuery('#' + toggleId).removeClass(toggleOn).addClass(toggleOff);
                }
            } else {
                if (action == 'remove') {
                    //switch it on
                    mQuery('#' + toggleId).removeClass(toggleOn).addClass(toggleOff);
                } else {
                    mQuery('#' + toggleId).removeClass(toggleOff).addClass(toggleOn);
                }
            }
        },
        error: function (request, textStatus, errorThrown) {
            //return the icon back
            mQuery('#' + toggleId).removeClass(spinClass);

            if (action == 'remove') {
                //switch it on
                mQuery('#' + toggleId).removeClass(toggleOff).addClass(toggleOn);
            } else {
                mQuery('#' + toggleId).removeClass(toggleOn).addClass(toggleOff);
            }
        }
    });
};

MailVotech.leadNoteOnLoad = function (container, response) {
    if (response.noteHtml) {
        var el = '#LeadNote' + response.noteId;
        if (mQuery(el).length) {
            mQuery(el).replaceWith(response.noteHtml);
        } else {
            mQuery('#LeadNotes').prepend(response.noteHtml);
        }

        MailVotech.makeModalsAlive(mQuery(el + " *[data-toggle='ajaxmodal']"));
        MailVotech.makeConfirmationsAlive(mQuery(el+' a[data-toggle="confirmation"]'));
        MailVotech.makeLinksAlive(mQuery(el + " a[data-toggle='ajax']"));
    } else if (response.deleteId && mQuery('#LeadNote' + response.deleteId).length) {
        mQuery('#LeadNote' + response.deleteId).remove();
    }

    if (response.upNoteCount || response.noteCount || response.downNoteCount) {
        var noteCountWrapper = mQuery('#NoteCount');
        var count = parseInt(noteCountWrapper.text().trim());

        if (response.upNoteCount) {
            count++;
        } else if (response.downNoteCount) {
            count--;
        } else {
            count = parseInt(response.noteCount);
        }

        noteCountWrapper.text(count);
    }
};

MailVotech.showSocialMediaImageModal = function(imgSrc) {
    mQuery('#socialImageModal img').attr('src', imgSrc);
    mQuery('#socialImageModal').modal('show');
};

MailVotech.leadImportOnLoad = function (container, response) {
    if (!mQuery('#leadImportProgress').length) {
        MailVotech.clearModeratedInterval('leadImportProgress');
    } else {
        MailVotech.setModeratedInterval('leadImportProgress', 'reloadLeadImportProgress', 3000);
    }
};

MailVotech.reloadLeadImportProgress = function() {
    if (!mQuery('#leadImportProgress').length) {
        MailVotech.clearModeratedInterval('leadImportProgress');
    } else {
        // Get progress separate so there's no delay while the import batches
        MailVotech.ajaxActionRequest('lead:getImportProgress', {}, function(response) {
            if (response.progress) {
                if (response.progress[0] > 0) {
                    mQuery('.imported-count').html(response.progress[0]);
                    mQuery('.progress-bar-import').attr('aria-valuenow', response.progress[0]).css('width', response.percent + '%');
                    mQuery('.progress-bar-import span.sr-only').html(response.percent + '%');
                }
            }
        }, false, false, "GET");

        // Initiate import
        mQuery.ajax({
            showLoadingBar: false,
            url: window.location + '?importbatch=1',
            success: function(response) {
                MailVotech.moderatedIntervalCallbackIsComplete('leadImportProgress');

                if (response.newContent) {
                    // It's done so pass to process page
                    MailVotech.processPageContent(response);
                }
            }
        });
    }
};

MailVotech.removeBounceStatus = function (el, dncId, channel) {
    mQuery(el).removeClass('ri-close-line').addClass('ri-loader-3-line ri-spin');

    MailVotech.ajaxActionRequest('lead:removeBounceStatus', {'id': dncId, 'channel': channel}, function() {
        mQuery('#bounceLabel' + dncId).tooltip('destroy');
        mQuery('#bounceLabel' + dncId).fadeOut(300, function() { mQuery(this).remove(); });
    });
};

/**
 * Confirm callback for removing a tag from a contact
 */
MailVotech.confirmRemoveTagFromLead = function (action, el) {
    let element = mQuery(el);

    let leadId = element.data('lead-id');
    let tagId = element.data('tag-id');

    element.find('i')
        .removeClass('ri-close-line')
        .addClass('ri-loader-3-line ri-spin');

    MailVotech.ajaxActionRequest(
        'lead:removeTagFromLead',
        { leadId, tagId },
        function () {
            mQuery('#tagLabel' + tagId).fadeOut(300, function () {
                mQuery(this).remove();
            });
        }
    );

    // Dismiss the confirmation modal
    MailVotech.dismissConfirmation();
};


MailVotech.removeTagFromLead = function (el, leadId, tagId, event) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }

    let element = mQuery(el);

    element.data({
        'message': MailVotech.translate('mailvotech.lead.tag.confirm_remove'),
        'confirm-text': MailVotech.translate('mailvotech.core.form.confirm'),
        'cancel-text': MailVotech.translate('mailvotech.core.form.cancel'),
        'confirm-callback': 'confirmRemoveTagFromLead',
        'lead-id': leadId,
        'tag-id': tagId
    });

    MailVotech.showConfirmation(el);
};

MailVotech.toggleLiveLeadListUpdate = function () {
    if (typeof MailVotechVars.moderatedIntervals['leadListLiveUpdate'] == 'undefined') {
        MailVotech.setModeratedInterval('leadListLiveUpdate', 'updateLeadList', 5000);
        mQuery('#liveModeButton').addClass('active');
    } else {
        MailVotech.clearModeratedInterval('leadListLiveUpdate');
        mQuery('#liveModeButton').removeClass('active');
    }
};

MailVotech.updateLeadList = function () {
    var maxLeadId = mQuery('#liveModeButton').data('max-id');
    mQuery.ajax({
        url: mailvotechAjaxUrl,
        type: "get",
        data: "action=lead:getNewLeads&maxId=" + maxLeadId,
        dataType: "json",
        success: function (response) {
            if (response.leads) {
                if (response.indexMode == 'list') {
                    mQuery('#leadTable tbody').prepend(response.leads);
                } else {
                    if (mQuery('.shuffle-grid').length) {
                        //give a slight delay in order for images to load so that shuffle starts out with correct dimensions
                        var Shuffle = window.Shuffle,
                            element = document.querySelector('.shuffle-grid'),
                            shuffleOptions = {
                                itemSelector: '.shuffle-item'
                            };

                        // Using global variable to make it available outside of the scope of this function
                        window.leadsShuffleInstance = new Shuffle(element, shuffleOptions);
                        var items = mQuery(response.leads);
                        mQuery('.shuffle-grid').prepend(items);
                        window.leadsShuffleInstance.shuffle('appended', items.children(shuffleOptions.itemSelector).toArray());
                        window.leadsShuffleInstance.shuffle('update');
                    }

                    mQuery('#liveModeButton').data('max-id', response.maxId);
                }
            }

            if (typeof IdleTimer != 'undefined' && !IdleTimer.isIdle()) {
                // Remove highlighted classes
                if (response.indexMode == 'list') {
                    mQuery('#leadTable tr.warning').each(function() {
                        var that = this;
                        setTimeout(function() {
                            mQuery(that).removeClass('warning', 1000)
                        }, 5000);
                    });
                } else {
                    mQuery('.shuffle-grid .highlight').each(function() {
                        var that = this;
                        setTimeout(function() {
                            mQuery(that).removeClass('highlight', 1000, function() {
                                mQuery(that).css('border-top-color', mQuery(that).data('color'));
                            })
                        }, 5000);
                    });
                }
            }

            if (response.maxId) {
                mQuery('#liveModeButton').data('max-id', response.maxId);
            }

            MailVotech.moderatedIntervalCallbackIsComplete('leadListLiveUpdate');
        },
        error: function (request, textStatus, errorThrown) {
            MailVotech.processAjaxError(request, textStatus, errorThrown);

            MailVotech.moderatedIntervalCallbackIsComplete('leadListLiveUpdate');
        }
    });
};

MailVotech.toggleAnonymousLeads = function() {
    var searchValue = mQuery('#list-search').typeahead('val');
    var string      = mQuery('#anonymousLeadButton').data('anonymous').toLowerCase();

    if (searchValue.toLowerCase().indexOf('!' + string) == 0) {
        searchValue = searchValue.replace('!' + string, string);
        mQuery('#anonymousLeadButton').addClass('btn-primary');
    } else if (searchValue.toLowerCase().indexOf(string) == -1) {
        if (searchValue) {
            searchValue = searchValue + ' ' + string;
        } else {
            searchValue = string;
        }
        mQuery('#anonymousLeadButton').addClass('btn-primary');
    } else {
        searchValue = mQuery.trim(searchValue.replace(string, ''));
        mQuery('#anonymousLeadButton').removeClass('btn-primary');
    }
    searchValue = searchValue.replace("  ", " ");
    MailVotech.setSearchFilter(null, 'list-search', searchValue);
};

MailVotech.getLeadEmailContent = function (el) {
    var id = (mQuery.type( el ) === "string") ? el : mQuery(el).attr('id');
    MailVotech.activateLabelLoadingIndicator(id);

    var inModal = mQuery('#'+id).closest('modal').length;
    if (inModal) {
        mQuery('#MailVotechSharedModal .btn-primary').prop('disabled', true);
    }

    MailVotech.ajaxActionRequest('lead:getEmailTemplate', {'template': mQuery(el).val()}, function(response) {
        if (inModal) {
            mQuery('#MailVotechSharedModal .btn-primary').prop('disabled', false);
        }
        var idPrefix = id.replace('templates', '');
        var bodyEl = (mQuery('#'+idPrefix+'message').length) ? '#'+idPrefix+'message' : '#'+idPrefix+'body';

        ckEditors.get( mQuery(bodyEl)[0] ).setData(response.body);

        mQuery(bodyEl).val(response.body);
        mQuery('#'+idPrefix+'subject').val(response.subject);

        MailVotech.removeLabelLoadingIndicator();
    }, false, false, "GET");
};

MailVotech.updateLeadTags = function () {
    MailVotech.activateLabelLoadingIndicator('lead_tags_tags');
    var formData = mQuery('form[name="lead_tags"]').serialize();
    MailVotech.ajaxActionRequest('lead:updateLeadTags', formData, function(response) {
        if (response.tags) {
            mQuery('#lead_tags_tags').html(response.tags);
            mQuery('#lead_tags_tags').trigger('chosen:updated');
        }
        MailVotech.removeLabelLoadingIndicator();
    });
};

MailVotech.createLeadTag = function (el) {
    var newFound = false;
    mQuery('#' + mQuery(el).attr('id') + ' :selected').each(function(i, selected) {
        if (!mQuery.isNumeric(mQuery(selected).val())) {
            newFound = true;
        }
    });

    if (!newFound) {
        return;
    }

    MailVotech.activateLabelLoadingIndicator(mQuery(el).attr('id'));

    var tags = JSON.stringify(mQuery(el).val());

    MailVotech.ajaxActionRequest('lead:addLeadTags', {tags: tags}, function(response) {
        if (response.tags) {
            mQuery('#' + mQuery(el).attr('id')).html(response.tags);
            mQuery('#' + mQuery(el).attr('id')).trigger('chosen:updated');
        }

        MailVotech.removeLabelLoadingIndicator();
    });
};

MailVotech.createLeadUtmTag = function (el) {
    var newFound = false;
    mQuery('#' + mQuery(el).attr('id') + ' :selected').each(function(i, selected) {
        if (!mQuery.isNumeric(mQuery(selected).val())) {
            newFound = true;
        }
    });

    if (!newFound) {
        return;
    }

    MailVotech.activateLabelLoadingIndicator(mQuery(el).attr('id'));

    var utmtags = JSON.stringify(mQuery(el).val());

    MailVotech.ajaxActionRequest('lead:addLeadUtmTags', {utmtags: utmtags}, function(response) {
        if (response.tags) {
            mQuery('#' + mQuery(el).attr('id')).html(response.utmtags);
            mQuery('#' + mQuery(el).attr('id')).trigger('chosen:updated');
        }

        MailVotech.removeLabelLoadingIndicator();
    });
};

MailVotech.leadBatchSubmit = function() {
    const findReplacePrefix = mQuery('#lead_batch_find_replace_field').length ? 'lead_batch_find_replace' : 'find_replace';
    const findReplaceAll = mQuery('#' + findReplacePrefix + '_all').val() === '1';
    if (findReplaceAll || MailVotech.batchActionPrecheck()) {
        if (mQuery('#lead_batch_remove').val() || mQuery('#lead_batch_add').val() || mQuery('#lead_batch_dnc_reason').length || mQuery('#lead_batch_stage_addstage').length || mQuery('#lead_batch_owner_addowner').length || mQuery('#' + findReplacePrefix + '_field').val() || mQuery('#contact_channels_ids').length || mQuery('#batch_tag_tags_add_tags').val() || mQuery('#batch_tag_tags_remove_tags').val()) {
            const ids = MailVotech.getCheckedListIds(false, true);

            if (mQuery('#lead_batch_ids').length) {
                mQuery('#lead_batch_ids').val(ids);
            } else if (mQuery('#lead_batch_dnc_reason').length) {
                mQuery('#lead_batch_dnc_ids').val(ids);
            } else if (mQuery('#lead_batch_stage_addstage').length) {
                mQuery('#lead_batch_stage_ids').val(ids);
            } else if (mQuery('#lead_batch_owner_addowner').length) {
                mQuery('#lead_batch_owner_ids').val(ids);
            } else if (mQuery('#' + findReplacePrefix + '_field').length) {
                mQuery('#' + findReplacePrefix + '_ids').val(ids);
            } else if (mQuery('#contact_channels_ids').length) {
                mQuery('#contact_channels_ids').val(ids);
            } else if (mQuery('#batch_tag_ids').length) {
                mQuery('#batch_tag_ids').val(ids);
            }

            return true;
        }

    }

    mQuery('#MailVotechSharedModal').modal('hide');

    return false;
};

MailVotech.refreshFindReplaceList = function(response) {
    const modalTarget = response.modalId ? '#' + response.modalId : '#MailVotechSharedModal';

    if (mQuery(modalTarget).length) {
        mQuery('body').removeClass('noscroll modal-open');
        mQuery(modalTarget).modal('hide');
        mQuery('.modal-backdrop').remove();
    }

    MailVotech.loadContent(globalThis.location.href);
};

MailVotech.updateLeadFieldValues = function (field) {
    mQuery('.condition-custom-date-row').hide();
    MailVotech.updateFieldOperatorValue(field, 'lead:updateLeadFieldValues', MailVotech.updateLeadFieldValueOptions, [true]);
};

MailVotech.updateLeadFieldValueOptions = function (field, updating) {
    var fieldId = mQuery(field).attr('id');
    var fieldPrefix = fieldId.slice(0, -5);

    if ('date' === mQuery('#'+fieldPrefix + 'operator').val()) {
        var customOption = mQuery(field).find('option[data-custom=1]');
        var value        = mQuery(field).val();

        var customSelected = mQuery(customOption).prop('selected');
        if (customSelected) {
            if (!updating) {
                // -/+ P/PT number unit
                var regex = /(\+|-)(PT?)([0-9]*)([DMHY])$/g;
                var match = regex.exec(value);
                if (match) {
                    var interval = ('-' === match[1]) ? match[1] + match[3] : match[3];
                    var unit = ('PT' === match[2] && 'M' === match[4]) ? 'i' : match[4];

                    mQuery('#lead-field-custom-date-interval').val(interval);
                    mQuery('#lead-field-custom-date-unit').val(unit.toLowerCase());
                }
            } else {
                var interval = mQuery('#lead-field-custom-date-interval').val();
                var unit = mQuery('#lead-field-custom-date-unit').val();

                // Convert interval/unit into PHP a DateInterval format
                var prefix = ("i" == unit || "h" == unit) ? "PT" : "P";
                // DateInterval uses M for minutes instead of i
                if ("i" === unit) {
                    unit = "m";
                }

                unit = unit.toUpperCase();

                var operator = "+";
                if (parseInt(interval) < 0) {
                    operator = "-";
                    interval = -1 * parseInt(interval);
                }
                var newValue = operator + prefix + interval + unit;
                customOption.attr('value', newValue);
            }
            mQuery('.condition-custom-date-row').show();
        } else {
            mQuery('.condition-custom-date-row').hide();
        }
    } else {
        mQuery('.condition-custom-date-row').hide();
    }
};

MailVotech.toggleTimelineMoreVisiblity = function (el) {
    if (mQuery(el).is(':visible')) {
        mQuery(el).slideUp('fast');
        mQuery(el).next().text(mailvotechLang['showMore']);
    } else {
        mQuery(el).slideDown('fast');
        mQuery(el).next().text(mailvotechLang['hideMore']);
    }
};

MailVotech.displayUniqueIdentifierWarning = function (el) {
    if (mQuery(el).val() === "0") {
        mQuery('.unique-identifier-warning').fadeOut('fast');
    } else {
        mQuery('.unique-identifier-warning').fadeIn('fast');
    }
};

MailVotech.initUniqueIdentifierFields = function() {
    var uniqueFields = mQuery('[data-unique-identifier]');
    if (uniqueFields.length) {
        uniqueFields.on('change', function() {
            var input = mQuery(this);
            var request = {
                field: input.data('unique-identifier'),
                value: input.val(),
                ignore: mQuery('#lead_unlockId').val()
            };
            MailVotech.ajaxActionRequest('lead:getLeadIdsByFieldValue', request, function(response) {
                if (response.items !== 'undefined' && response.items.length) {
                    var warning = mQuery('<div class="exists-warning" />').text(response.existsMessage);
                    mQuery.each(response.items, function(i, item) {
                        if (i > 0) {
                            warning.append(mQuery('<span>, </span>'));
                        }

                        var link = mQuery('<a/>')
                            .attr('href', item.link)
                            .attr('target', '_blank')
                            .text(item.name+' ('+item.id+')');
                        warning.append(link);
                    });
                    warning.appendTo(input.parent());
                } else {
                    input.parent().find('div.exists-warning').remove();
                }
            }, false, false, "GET");
        });
    }
};

MailVotech.updateFilterPositioning = function (el) {
    var $el       = mQuery(el);
    var $parentEl = $el.closest('.filter--row');
    var list      = $parentEl.parent().children('.filter--row');
    const isFirst = list.index($parentEl) === 0;

    if (isFirst) {
        $el.val('and');
    }

    if ($el.val() === 'and' && !isFirst) {
        $parentEl.addClass('in-group');
    } else {
        $parentEl.removeClass('in-group');
    }
};

MailVotech.setAsPrimaryCompany = function (companyId,leadId){
    MailVotech.ajaxActionRequest('lead:setAsPrimaryCompany', {'companyId': companyId, 'leadId': leadId}, function(response) {
        if (response.success) {
            // Update the company icon
            mQuery('.panel-companies .ri-user-star-fill').removeClass('ri-user-star-fill');
            mQuery('.panel-companies .contained-list-item__content[href$="/' + response.newPrimary + '"]').find('i').addClass('ri-user-star-fill');
        }
    });
};

MailVotech.handleAssetDownloadSearch = function(filterNum, fieldObject, fieldAlias, operator, resultHtml, search) {
    var assetDownloadFilter = mQuery('#leadlist_filters_' + filterNum + '_properties_filter');
    var assetDownloadInput = mQuery('#leadlist_filters_' + filterNum + '_properties input');
    var assetDownloadProperties = mQuery('#leadlist_filters_' + filterNum + '_properties');
    assetDownloadFilter.on('chosen:no_results', function () {
        var search = assetDownloadInput.val();
        mQuery('#leadlist_filters_' + filterNum + '_properties .chosen-drop').remove();
        clearTimeout(mQuery.data(this, 'timer'));
        var existingOptions = mQuery('#leadlist_filters_' + filterNum + '_properties_filter option');
        mQuery(assetDownloadProperties).data('existing-options', existingOptions);
        mQuery(this).data('timer', setTimeout(function () {
            assetDownloadInput.width('auto').prop('disabled', true).val(MailVotech.translate('mailvotech.core.lookup.loading_data'));
            MailVotech.loadFilterForm(filterNum, fieldObject, fieldAlias, operator, resultHtml, search)
        }, 1000, search))
    });
    var existingOptions = mQuery(assetDownloadProperties).data('existing-options');
    assetDownloadFilter.append(existingOptions);
    assetDownloadFilter.trigger('chosen:updated');
    if (mQuery('#leadlist_filters_' + filterNum + '_properties_filter option').length === 0 ) {
        assetDownloadInput.val(mailvotechLang['chosenNoResults']);
    }
    else if (search !== null) {
        assetDownloadFilter.trigger('chosen:open.chosen')
    }
};

MailVotech.listOnLoad = function(container, response) {
    MailVotech.loadAndProcessPageContent('#contacts-container');

    const segmentDependenciesTab = mQuery('a#segment-dependencies');
    let segmentDependenciesLoaded = false;
    let jsPlumbData = null;

    if (segmentDependenciesTab.length) {
        mQuery(document).on('shown.bs.tab', 'a[data-toggle="tab"]', function (e) {
            if (!mQuery(e.target).attr('id') === 'segment-dependencies') {
                return;
            }

            if (!segmentDependenciesLoaded) {
                segmentDependenciesLoaded = true;
                mQuery.ajax({
                    showLoadingBar: true,
                    url: mailvotechAjaxUrl,
                    type: 'GET',
                    data: {
                        action: 'lead:getSegmentDependencyTree',
                        id: mQuery('input#entityId').val()
                    },
                    dataType: 'json',
                    success: function (response) {
                        MailVotech.stopPageLoadingBar();
                        MailVotech.renderSegmentTree('#segment-dependencies-container', response);
                        jsPlumbData = response;
                    },
                    error: function (request, textStatus, errorThrown) {
                        MailVotech.processAjaxError(request, textStatus, errorThrown);
                    }
                });
            } else if (jsPlumbData) {
                MailVotech.renderSegmentTree('#segment-dependencies-container', jsPlumbData);
            }
        });

        mQuery(document).on('hide.bs.tab', 'a[data-toggle="tab"]', function (e) {
            if (!mQuery(e.target).attr('id') !== 'segment-dependencies') {
                MailVotech.cleanSegmentDependencies();
            }
        });
    }
};

MailVotech.listOnUnload = function() {
    MailVotech.cleanSegmentDependencies();
}

/**
 *  JsPlumb has a problem with z-index when using tabs or change content by ajax so we need to re-initialize it.
 */
MailVotech.cleanSegmentDependencies = function() {
    mQuery('.jtk-connector').remove();
    mQuery('#segment-dependencies-container').empty();
}

MailVotech.renderSegmentTree = function(containerId, data) {
    MailVotech.cleanSegmentDependencies(); // Make sure there is no tree rendered already

    const plumbInstance = jsPlumb.getInstance({
        elementsDraggable:false,
        container: document.querySelector(containerId)
    });

    const wrapper = mQuery(containerId);
    const nodes = {};

    for (let level = 0; level < data.levels.length; level++) {
        const row = mQuery('<div class="segment-level" id="segment-level-'+level+'"></div>');
        wrapper.append(row);
        for (let index = 0; index < data.levels[level].nodes.length; index++) {
            const nodeData = data.levels[level].nodes[index];
            const node = MailVotech.buildSegmentDependencyNode(nodeData);
            row.append(node);
            nodes[nodeData['id']] = node;
        }
    }

    for (let index = 0; index < data.edges.length; index++) {
        const edge = data.edges[index];
        plumbInstance.connect({
            source:nodes[edge.source],
            target:nodes[edge.target],
            connector: 'Flowchart',
            anchor: ['Top', 'Bottom'],
            endpoint:"Blank",
        });
    }

    return plumbInstance;
}

MailVotech.buildSegmentDependencyNode = function(nodeData) {
    let message = '';
    let hasMessageClass = '';

    if (nodeData['message']) {
        message = '<span class="segment-dependency-message text-danger">'+nodeData['message']+'</span>';
        hasMessageClass = ' has-message';
    }

    const link = '<a href="'+nodeData['link']+'" data-toggle="ajax">'+nodeData['name']+'</a>';

    const node = mQuery('<div class="segment-node'+hasMessageClass+'" id="segment-node'+nodeData['id']+'">'+link+message+'</div>');

    return node;
}

MailVotech.loadAndProcessPageContent = function(containerId) {
    const container = mQuery(containerId);

    // Load the contacts only if the container exists.
    if (!container.length) {
        return;
    }

    const segmentContactUrl = container.data('target-url');
    mQuery.get(segmentContactUrl, function(response) {
        response.target = containerId;
        MailVotech.processPageContent(response);
    });
};

MailVotech.lazyLoadContactStatsOnLeadLoad = function() {
    const containerId = '#lead-stats';
    const container = mQuery(containerId);

    // Load the contact stats only if the container exists.
    if (!container.length) {
        return;
    }

    const contactStatsUrl = container.data('target-url');
    mQuery.get(contactStatsUrl, function(response) {
        response.target = containerId;
        MailVotech.processPageContent(response);
    });
};
