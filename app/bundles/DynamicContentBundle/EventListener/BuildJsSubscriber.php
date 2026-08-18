<?php

namespace MailVotech\DynamicContentBundle\EventListener;

use MailVotech\CoreBundle\CoreEvents;
use MailVotech\CoreBundle\Event\BuildJsEvent;
use MailVotech\CoreBundle\Event\BuildJsScope;
use MailVotech\CoreBundle\Twig\Helper\AssetsHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class BuildJsSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private AssetsHelper $assetsHelper,
        private TranslatorInterface $translator,
        private RequestStack $requestStack,
        private RouterInterface $router,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CoreEvents::BUILD_MAILVOTECH_JS => ['onBuildJs', 200],
        ];
    }

    /**
     * Adds DWC helpers after page-event setup and before page-event delivery.
     */
    public function onBuildJs(BuildJsEvent $event): void
    {
        $dwcUrl = $this->router->generate('mailvotech_api_dynamicContent_action', ['objectAlias' => 'slotNamePlaceholder'], UrlGeneratorInterface::ABSOLUTE_URL);

        $js = <<<JS
        
           // call variable if doesnt exist
            if (typeof MailVotechDomain == 'undefined') {
                var MailVotechDomain = '{$this->requestStack->getCurrentRequest()->getSchemeAndHttpHost()}';
            }            
            if (typeof MailVotechLang == 'undefined') {
                var MailVotechLang = {
                     'submittingMessage': "{$this->translator->trans('mailvotech.form.submission.pleasewait')}"
        };
            }
MailVotechJS.replaceDynamicContent = function (params) {
    params = params || {};

    var dynamicContentSlots = document.querySelectorAll('.mailvotech-slot, [data-slot="dwc"]');
    if (dynamicContentSlots.length) {
        MailVotechJS.iterateCollection(dynamicContentSlots)(function(node, i) {
            var slotName = node.dataset['slotName'];
            if ('undefined' === typeof slotName) {
                slotName = node.dataset['paramSlotName'];
            }
            if ('undefined' === typeof slotName) {
                node.innerHTML = '';
                return;
            }
            var url = '{$dwcUrl}'.replace('slotNamePlaceholder', slotName);

            MailVotechJS.makeCORSRequest('GET', url, params, function(response, xhr) {
                if (response.content) {
                    var dwcContent = response.content;
                    node.innerHTML = dwcContent;

                    MailVotechJS.onDynamicContentResponse(response);
                    MailVotechJS.enhanceDynamicContent(dwcContent);
                }
            });
        });
    }
};

// Tracking overrides this hook before PageBundle drains the pre-delivery queue.
MailVotechJS.onDynamicContentResponse = function() {};

MailVotechJS.enhanceDynamicContent = function(dwcContent) {
    // form load library
    MailVotechJS.initializeForms(dwcContent);

    var m;
    var regEx = /<script[^>]+src="?([^"\s]+)"?\s/g;

    while (m = regEx.exec(dwcContent)) {
        if ((m[1]).search("/focus/") > 0) {
            MailVotechJS.insertScript(m[1]);
        }
    }
};

MailVotechJS.initializeForms = function(content) {
    if (content.search("mailvotechform_wrapper") !== -1) {
        // if doesn't exist
        if (typeof MailVotechSDK == 'undefined') {
            if (typeof MailVotechSDKLoaded == 'undefined') {
                window.MailVotechSDKLoaded = true;
                MailVotechJS.insertScript('{$this->assetsHelper->getUrl('media/js/mailvotech-form.js', null, null, true)}');

                // check initialize form library
                var fileInterval = setInterval(function() {
                    if (typeof MailVotechSDK != 'undefined') {
                        MailVotechSDK.onLoad();
                        clearInterval(fileInterval); // clear interval
                     }
                 }, 100); // check every 100ms
            }
        } else {
            MailVotechSDK.onLoad();
         }
    }
};

MailVotechJS.documentReady(function() {
    var fallbackForms = document.querySelectorAll('.mailvotech-slot form[data-mailvotech-form], [data-slot="dwc"] form[data-mailvotech-form]');
    var initializationRequested = false;
    MailVotechJS.iterateCollection(fallbackForms)(function(form) {
        var formId = form.getAttribute('data-mailvotech-form');
        if (!initializationRequested && !document.getElementById('mailvotechform_' + formId + '_messenger')) {
            initializationRequested = true;
            MailVotechJS.initializeForms('mailvotechform_wrapper');
        }
    });
});

MailVotechJS.beforeFirstEventDelivery(MailVotechJS.replaceDynamicContent);
JS;
        $event->appendJsForScope($js, BuildJsScope::ESSENTIAL, 'MailVotech Dynamic Content');

        $js = <<<'JS_WRAP'
        (function(window) {
        var MailVotechJS = window.MailVotechJS;
        if (!MailVotechJS || MailVotechJS.runtimeReady !== true) {
            return;
        }
        
        MailVotechJS.onDynamicContentResponse = function(response) {
            if (response.id && response.sid) {
                MailVotechJS.setTrackedContact(response);
            }
        };
        })(window);
        JS_WRAP;
        $event->appendJsForScope($js, BuildJsScope::TRACKING, 'MailVotech Dynamic Content Tracking');
    }
}
