// DashboardBundle
// Use absolute path to keep dashboard working when app is in subdir
MailVotech.widgetUrl = mailvotechBasePath + '/s/dashboard/widget/';

/**
 * @type jQuery DOM element to be replaced with spinner
 */
MailVotech.dashboardSubmitButton = false; // Button text, to be get and shown instead of spinner

/**
 * Init dashboard events
 * @param container
 */
MailVotech.dashboardOnLoad = function (container) {
    MailVotech.loadWidgets();
};

/**
 * Load all widgets on initial page render
 */
MailVotech.loadWidgets = function () {
    MailVotech.dashboardFilterPreventSubmit();

    jQuery('.widget').each(function() {
        let widgetId = jQuery(this).attr('data-widget-id');
        let container = jQuery('.widget[data-widget-id="'+widgetId+'"]');
        jQuery.ajax({
            url: MailVotech.widgetUrl+widgetId+'?ignoreAjax=true',
        }).done(function(response) {
            MailVotech.widgetOnLoad(container, response);
        });
    });

    jQuery(document).ajaxComplete(function(){
        MailVotech.initDashboardFilter();
    });
};

/**
 * Init dashboard filter events after widget load
 */
MailVotech.initDashboardFilter = function () {
    let form = jQuery('form[name="daterange"]');
    form.find('button')
        .replaceWith(MailVotech.dashboardSubmitButton);
    form
        .unbind('submit')
        .on('submit', function(e){
            e.preventDefault();
            MailVotech.dashboardFilterPreventSubmit();
            jQuery('.widget').each(function() {
                let widgetId = jQuery(this).attr('data-widget-id');
                let element = jQuery('.widget[data-widget-id="' + widgetId + '"]');
                jQuery.ajax({
                    type: 'POST',
                    url: MailVotech.widgetUrl + widgetId + '?ignoreAjax=true',
                    data: form.serializeArray(),
                    success: function (response) {
                        MailVotech.widgetOnLoad(element, response);
                    }
                });
            });
        });
};

/**
 * Prevent filter from submit, show spinner instead of send button
 */
MailVotech.dashboardFilterPreventSubmit = function() {
    let form = jQuery('form[name="daterange"]');
    let button = form.find('button:first');
    MailVotech.dashboardSubmitButton = button.clone();
    button.width(button.width()+'px'); // Keep button width
    button.html('<i class="ri-loader-3-line ri-spin"></i>');
    jQuery('.widget').find('.card-body').html('<div class="spinner"><i class="ri-loader-3-line ri-spin"></i></div>');
    form
        .unbind('submit')
        .on('submit', function(e){
            e.preventDefault();
        });
};

MailVotech.dashboardOnUnload = function(id) {
    // Trash initialized dashboard vars on app content change.
    mQuery('.jvectormap-tip').remove();
};

/**
 * Render widget from XHR to DOM
 *
 * @param container
 * @param response
 */
MailVotech.widgetOnLoad = function(container, response) {
    if (!response.widgetId) return;
    // target in DOM
    var widget = mQuery('.widget[data-widget-id="' + response.widgetId + '"]');
    // source from response
    var widgetHtml = mQuery(response.widgetHtml);

    // initialize edit button modal again
    widgetHtml.find("*[data-toggle='ajaxmodal']").on('click.ajaxmodal', function (event) {
        event.preventDefault();
        MailVotech.ajaxifyModal(this, event);
    });

    // Create the new widget wrapper and add it to the 0 position if doesn't exist (probably a new one)
    if (!widget.length) {
        widget = mQuery('<div/>')
            .addClass('widget')
            .attr('data-widget-id', response.widgetId);
        mQuery('#dashboard-widgets').prepend(widget);
    }

    widget.html(widgetHtml)
        .css('width', response.widgetWidth + '%')
        .css('height', response.widgetHeight + '%');
    MailVotech.renderCharts(widgetHtml);

    const map = widgetHtml.find('.vector-map').first();
    if (map.length && !map.hasClass('map-rendered')) {
        MailVotech.initMap(widgetHtml, 'regions');
    }

    MailVotech.initWidgetRemoveEvents();
    MailVotech.initWidgetSorting();
    MailVotech.initDashboardFilter();
};

MailVotech.initWidgetRemoveEvents = function () {
    jQuery('.remove-widget')
        .unbind('click')
        .on('click', function(e) {
            e.preventDefault();
            element = jQuery(this);
            let url = element.attr('href');
            element.closest('.widget').remove();
            jQuery.ajax({
                url: url,
            });
        });
};

MailVotech.initWidgetSorting = function () {
    var widgetsWrapper = mQuery('#dashboard-widgets');
    var bodyOverflow = {};

    widgetsWrapper.sortable({
        handle: '.card-header h4',
        placeholder: 'sortable-placeholder',
        items: '.widget',
        opacity: 0.9,
        scroll: true,
        scrollSpeed: 10,
        tolerance: "pointer",
        cursor: 'move',
        appendTo: '#dashboard-widgets',

        helper: function(e, ui) {
            // Ensure the draggable retains it's original size and that the margin doesn't cause things to bounce around
            ui.children().each(function() {
                mQuery(this).width(mQuery(this).width());
                mQuery(this).height(mQuery(this).height());
            });

            // Fix body overflow that messes sortable up
            bodyOverflow.overflowX = mQuery('body').css('overflow-x');
            bodyOverflow.overflowY = mQuery('body').css('overflow-y');
            mQuery('body').css({
                overflowX: 'visible',
                overflowY: 'visible'
            });

            mQuery("#dashboard-widgets .widget").each(function(i) {
                var item = mQuery(this);
                var item_clone = item.clone();

                var canvas = item.find('canvas').first();
                if (canvas.length) {
                    // Copy the canvas
                    var destCanvas = item_clone.find('canvas').first();
                    var destCtx = destCanvas[0].getContext('2d');
                    destCtx.drawImage(canvas[0], 0, 0);
                }

                item.data("clone", item_clone);
                var position = item.position();
                item_clone
                    .css({
                        left: position.left,
                        top: position.top,
                        width: item.width(),
                        visibility: "visible",
                        position: "absolute",
                        zIndex: 1
                    });

                item.css('visibility', 'hidden');
                mQuery("#cloned-widgets").append(item_clone);
            });

            return ui;
        },
        start: function(e, ui) {
            ui.helper.css('visibility', 'visible');
            ui.helper.data("clone").hide();
        },
        sort: function(e, ui) {
            var tile = ui.item.find('.tile').first();
            // Prevent margin from pushing the elements out of the way
            ui.placeholder.css({
                marginTop: "5px",
                marginBottom: "5px",
                marginLeft: 0,
                marginRight: 0
            });
        },
        stop: function() {
            // Restore original overflow
            mQuery('body').css(bodyOverflow);

            mQuery("#dashboard-widgets .widget.exclude-me").each(function() {
                var item = mQuery(this);
                var clone = item.data("clone");
                var position = item.position();

                clone.css("left", position.left);
                clone.css("top", position.top);
                clone.show();
                item.removeClass("exclude-me");
            });

            mQuery("#dashboard-widgets .widget").css("visibility", "visible");
            mQuery("#cloned-widgets .widget").remove();

            MailVotech.saveWidgetSorting();
        },
        change: function(e, ui) {
            mQuery("#dashboard-widgets .widget:not(.exclude-me)").each(function() {
                var item = mQuery(this);
                var clone = item.data("clone");
                clone.stop(true, false);
                var position = item.position();
                clone.animate({
                    left: position.left,
                    top: position.top
                }, 200);
            });
        }
    }).disableSelection();
}

MailVotech.saveWidgetSorting = function () {
    var widgetsWrapper = mQuery('#dashboard-widgets');
    var widgets = widgetsWrapper.children();
    var ordering = [];
    widgets.each(function(index, value) {
        ordering.push(mQuery(this).attr('data-widget-id'));
    });

    MailVotech.ajaxActionRequest('dashboard:updateWidgetOrdering', {'ordering': ordering}, function(response) {
        // @todo handle errors
    });
}

MailVotech.updateWidgetForm = function (element) {
    MailVotech.activateLabelLoadingIndicator('widget_type');
    var formWrapper = mQuery(element).closest('form');
    var WidgetFormValues = formWrapper.serializeArray();
    MailVotech.ajaxActionRequest('dashboard:updateWidgetForm', WidgetFormValues, function(response) {
        if (response.formHtml) {
            var formHtml = mQuery(response.formHtml);
            formHtml.find('#widget_buttons').addClass('hide hidden');
            formWrapper.html(formHtml.children());
            MailVotech.onPageLoad('#widget_params');
        }
        MailVotech.removeLabelLoadingIndicator();
    });
};

MailVotech.exportDashboardLayout = function(text, baseUrl) {
    var name = prompt(text, "");

    if (name !== null) {
        if (name) {
            baseUrl = baseUrl + "?name=" + encodeURIComponent(name);
        }

        window.location = baseUrl;
    }
};

MailVotech.saveDashboardLayout = function(text) {
    var name = prompt(text, "");

    if (name) {
        mQuery.ajax({
            type: 'POST',
            url: mailvotechBaseUrl+'s/dashboard/save',
            data: {name: name}
        });
    }
};
