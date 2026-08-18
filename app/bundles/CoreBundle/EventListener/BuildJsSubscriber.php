<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\EventListener;

use MailVotech\CoreBundle\CoreEvents;
use MailVotech\CoreBundle\Event\BuildJsEvent;
use MailVotech\CoreBundle\Event\BuildJsScope;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class BuildJsSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            CoreEvents::BUILD_MAILVOTECH_JS => ['onBuildJs', 1000],
        ];
    }

    /**
     * Adds the MailVotechJS definition and core
     * JS functions for use in Bundles. This
     * must retain top priority of 1000.
     */
    public function onBuildJs(BuildJsEvent $event): void
    {
        $js = <<<'JS_WRAP'
// Polyfill for CustomEvent to support IE 9+
(function () {
    if ( typeof window.CustomEvent === "function" ) return false;
    function CustomEvent ( event, params ) {
        params = params || { bubbles: false, cancelable: false, detail: undefined };
        var evt = document.createEvent( 'CustomEvent' );
        evt.initCustomEvent( event, params.bubbles, params.cancelable, params.detail );
        return evt;
    }
    CustomEvent.prototype = window.Event.prototype;
    window.CustomEvent = CustomEvent;
})();

var MailVotechJS = MailVotechJS || {};

MailVotechJS.serialize = function(obj) {
    if ('string' == typeof obj) {
        return obj;
    }

    return Object.keys(obj).map(function(key) {
        return encodeURIComponent(key) + '=' + encodeURIComponent(obj[key]);
    }).join('&');
};

MailVotechJS.documentReady = function(f) {
    /in/.test(document.readyState) ? setTimeout(function(){MailVotechJS.documentReady(f)}, 9) : f();
};

MailVotechJS.iterateCollection = function(collection) {
    return function(f) {
        for (var i = 0; collection[i]; i++) {
            f(collection[i], i);
        }
    };
};

MailVotechJS.log = function() {
    var log = {};
    log.history = log.history || [];

    log.history.push(arguments);

    if (window.console) {
        console.log(Array.prototype.slice.call(arguments));
    }
};

MailVotechJS.setCookie = function(name, value) {
    document.cookie = name+"="+value+"; path=/; secure";
};

MailVotechJS.createCORSRequest = function(method, url) {
    var xhr = new XMLHttpRequest();
    
    method = method.toUpperCase();
    
    if ("withCredentials" in xhr) {
        xhr.open(method, url, true);
    } else if (typeof XDomainRequest != "undefined") {
        xhr = new XDomainRequest();
        xhr.open(method, url);
    }
    
    return xhr;
};
MailVotechJS.CORSRequestsAllowed = true;
MailVotechJS.requestWithCredentials = false;
MailVotechJS.appendTrackedContact = function(data) {
    return data;
};
MailVotechJS.makeCORSRequest = function(method, url, data, callbackSuccess, callbackError) {
    // Tracking overrides this hook to append stored contact data.
    data = MailVotechJS.appendTrackedContact(data);
    
    var query = MailVotechJS.serialize(data);
    if (method.toUpperCase() === 'GET') {
        url = url + '?' + query;
        var query = '';
    }
    
    var xhr = MailVotechJS.createCORSRequest(method, url);
    var response;
    
    callbackSuccess = callbackSuccess || function(response, xhr) { };
    callbackError = callbackError || function(response, xhr) { };

    if (!xhr) {
        MailVotechJS.log('MailVotechJS.debug: Could not create an XMLHttpRequest instance.');
        return false;
    }

    if (!MailVotechJS.CORSRequestsAllowed) {
        callbackError({}, xhr);
        
        return false;
    }
    
    xhr.onreadystatechange = function (e) {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            response = MailVotechJS.parseTextToJSON(xhr.responseText);
            if (xhr.status === 200) {
                callbackSuccess(response, xhr);
            } else {
                callbackError(response, xhr);
               
                if (xhr.status === XMLHttpRequest.UNSENT) {
                    // Don't bother with further attempts
                    MailVotechJS.CORSRequestsAllowed = false;
                }
            }
        }
    };
   
    if (typeof xhr.setRequestHeader !== "undefined"){
        if (method.toUpperCase() === 'POST') {
            xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        }
    
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.withCredentials = MailVotechJS.requestWithCredentials;
    }
    xhr.send(query);
};

MailVotechJS.parseTextToJSON = function(maybeJSON) {
    var response;

    try {
        // handle JSON data being returned
        response = JSON.parse(maybeJSON);
    } catch (error) {
        response = maybeJSON;
    }

    return response;
};

MailVotechJS.insertScript = function (scriptUrl) {
    var scriptsInHead = document.getElementsByTagName('head')[0].getElementsByTagName('script');
    var lastScript    = scriptsInHead[scriptsInHead.length - 1];
    var scriptTag     = document.createElement('script');
    scriptTag.async   = 1;
    scriptTag.src     = scriptUrl;
    
    if (lastScript) {
        lastScript.parentNode.insertBefore(scriptTag, lastScript);
    } else {
        document.getElementsByTagName('head')[0].appendChild(scriptTag);
    }
};

MailVotechJS.insertStyle = function (styleUrl) {
    var linksInHead = document.getElementsByTagName('head')[0].getElementsByTagName('link');
    var lastLink    = linksInHead[linksInHead.length - 1];
    var linkTag     = document.createElement('link');
    linkTag.rel     = "stylesheet";
    linkTag.type    = "text/css";
    linkTag.href    = styleUrl;
    
    if (lastLink) {
        lastLink.parentNode.insertBefore(linkTag, lastLink.nextSibling);
    } else {
        document.getElementsByTagName('head')[0].appendChild(linkTag);
    }
};

MailVotechJS.guid = function () {
    function s4() {
        return Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1);
    }
    
    return s4() + s4() + '-' + s4() + '-' + s4() + '-' + s4() + '-' + s4() + s4() + s4();
};

MailVotechJS.dispatchEvent = function(name, detail) {
    var event = new CustomEvent(name, {detail: detail});
    document.dispatchEvent(event);
};

function s4() {
  return Math.floor((1 + Math.random()) * 0x10000)
    .toString(16)
    .substring(1);
}

MailVotechJS.preEventDeliveryQueue = [];
MailVotechJS.beforeFirstDeliveryMade = false;
MailVotechJS.beforeFirstEventDelivery = function(f) {
    MailVotechJS.preEventDeliveryQueue.push(f);
};

MailVotechJS.ensureEventContext = function(event, context0, context1) {
    return (typeof(event.detail) !== 'undefined'
        && event.detail[0] === context0
        && event.detail[1] === context1);
};

MailVotechJS.trackingEnabled = false;
// The aggregate build appends its tracking contribution after this readiness signal.
MailVotechJS.runtimeReady = true;
JS_WRAP;
        $event->appendJsForScope($js, BuildJsScope::RUNTIME, 'MailVotech Core Runtime');

        $js = <<<'JS_WRAP'
(function(window) {
var MailVotechJS = window.MailVotechJS;
if (!MailVotechJS || MailVotechJS.runtimeReady !== true) {
    if (window.console) {
        console.warn('MailVotech tracking requires the MailVotech essential runtime.');
    }
    return;
}

MailVotechJS.trackingEnabled = true;
MailVotechJS.requestWithCredentials = true;
MailVotechJS.mtcSet = false;
MailVotechJS.appendTrackedContact = function(data) {
    if (window.localStorage) {
        if (mtcId  = localStorage.getItem('mtc_id')) {
            data['mailvotech_device_id'] = localStorage.getItem('mailvotech_device_id');
        }              
    }
    
    return data;
};

MailVotechJS.getTrackedContact = function () {
    if (MailVotechJS.mtcSet) {
        // Already set
        return;
    }
    
    MailVotechJS.makeCORSRequest('GET', MailVotechJS.contactIdUrl, {}, function(response, xhr) {
        MailVotechJS.setTrackedContact(response);
    });
};

MailVotechJS.setTrackedContact = function(response) {
    if (response.id) {
        MailVotechJS.setCookie('mtc_id', response.id);
        MailVotechJS.setCookie('mailvotech_device_id', response.device_id);
        MailVotechJS.mtcSet = true;
            
        // Set the id in local storage in case cookies are only allowed for sites visited and MailVotech is on a different domain
        // than the current page
        try {
            localStorage.setItem('mtc_id', response.id);
            localStorage.setItem('mailvotech_device_id', response.device_id);
        } catch (e) {
            console.warn('Browser does not allow storing in local storage');
        }
    }
};

// Register events that should happen after the first event is delivered
MailVotechJS.postEventDeliveryQueue = [];
MailVotechJS.firstDeliveryMade      = false;
MailVotechJS.onFirstEventDelivery = function(f) {
    MailVotechJS.postEventDeliveryQueue.push(f);
};
document.addEventListener('mailvotechPageEventDelivered', function(e) {
    var detail   = e.detail;
    var isImage = detail.image;
    if (isImage && !MailVotechJS.mtcSet) {
        MailVotechJS.getTrackedContact();
    } else if (detail.response && detail.response.id) {
        MailVotechJS.setTrackedContact(detail.response);
    }
    
    if (!isImage && typeof detail.event[3] === 'object' && typeof detail.event[3].onload === 'function') {
       // Execute onload since this is ignored if not an image
       detail.event[3].onload(detail)       
    }
    
    if (!MailVotechJS.firstDeliveryMade) {
        MailVotechJS.firstDeliveryMade = true;
        for (var i = 0; i < MailVotechJS.postEventDeliveryQueue.length; i++) {
            if (typeof MailVotechJS.postEventDeliveryQueue[i] === 'function') {
                MailVotechJS.postEventDeliveryQueue[i](detail);
            }
            delete MailVotechJS.postEventDeliveryQueue[i];
        }
    }
});

/**
* Check if a DOM tracking pixel is present
*/
MailVotechJS.checkForTrackingPixel = function() {
    if (document.readyState !== 'complete') {
        // Periodically call self until the DOM is completely loaded
        setTimeout(function(){MailVotechJS.checkForTrackingPixel()}, 9)
    } else {
        // Only fetch once a tracking pixel has been loaded
        var maxChecks  = 3000; // Keep it from indefinitely checking in case the pixel was never embedded
        var checkPixel = setInterval(function() {
            if (maxChecks > 0 && !MailVotechJS.isPixelLoaded(true)) {
                // Try again
                maxChecks--;
                return;
            }
    
            clearInterval(checkPixel);
            
            if (maxChecks > 0) {
                // DOM image was found 
                var params = {}, hash;
                var hashes = MailVotechJS.trackingPixel.src.slice(MailVotechJS.trackingPixel.src.indexOf('?') + 1).split('&');

                for(var i = 0; i < hashes.length; i++) {
                    hash = hashes[i].split('=');
                    params[hash[0]] = hash[1];
                }

                MailVotechJS.dispatchEvent('mailvotechPageEventDelivered', {'event': ['send', 'pageview', params], 'params': params, 'image': true});
            }
        }, 1);
    }
}
MailVotechJS.checkForTrackingPixel();

MailVotechJS.isPixelLoaded = function(domOnly) {
    if (typeof domOnly == 'undefined') {
        domOnly = false;
    }
    
    if (typeof MailVotechJS.trackingPixel === 'undefined') {
        // Check the DOM for the tracking pixel
        MailVotechJS.trackingPixel = null;
        var imgs = Array.prototype.slice.apply(document.getElementsByTagName('img'));
        for (var i = 0; i < imgs.length; i++) {
            if (imgs[i].src.indexOf('mtracking.gif') !== -1) {
                MailVotechJS.trackingPixel = imgs[i];
                break;
            }
        }
    } else if (domOnly) {
        return false;
    }

    if (MailVotechJS.trackingPixel && MailVotechJS.trackingPixel.complete && MailVotechJS.trackingPixel.naturalWidth !== 0) {
        // All the browsers should be covered by this - image is loaded
        return true;
    }

    return false;
};

if (typeof window[window.MailVotechTrackingObject] !== 'undefined') {
    MailVotechJS.input = window[window.MailVotechTrackingObject];
    if (typeof MailVotechJS.input.q === 'undefined') {
        // In case mt() is not executed right away
        MailVotechJS.input.q = [];
    }
    MailVotechJS.inputQueue = MailVotechJS.input.q;

    // Dispatch the queue event when an event is added to the queue
    if (!MailVotechJS.inputQueue.hasOwnProperty('push')) {
        Object.defineProperty(MailVotechJS.inputQueue, 'push', {
            configurable: false,
            enumerable: false,
            writable: false,
            value: function () {
                for (var i = 0, n = this.length, l = arguments.length; i < l; i++, n++) {
                    MailVotechJS.dispatchEvent('eventAddedToMailVotechQueue', arguments[i]);
                }
                return n;
            }
        });
    }

    MailVotechJS.getInput = function(task, type) {
        var matches = [];
        if (typeof MailVotechJS.inputQueue !== 'undefined' && MailVotechJS.inputQueue.length) {
            for (var i in MailVotechJS.inputQueue) {
                if (MailVotechJS.inputQueue[i][0] === task && MailVotechJS.inputQueue[i][1] === type) {
                    matches.push(MailVotechJS.inputQueue[i]);
                }
            }
        }
        
        return matches; 
    }
}
})(window);
JS_WRAP;
        $event->appendJsForScope($js, BuildJsScope::TRACKING, 'MailVotech Core Tracking');
    }
}
