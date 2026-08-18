<?php

declare(strict_types=1);

namespace MailVotech\NotificationBundle\EventListener;

use MailVotech\CoreBundle\CoreEvents;
use MailVotech\CoreBundle\Event\BuildJsEvent;
use MailVotech\CoreBundle\Event\BuildJsScope;
use MailVotech\NotificationBundle\Helper\NotificationHelper;
use MailVotech\PluginBundle\Helper\IntegrationHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

final readonly class BuildJsSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private NotificationHelper $notificationHelper,
        private IntegrationHelper $integrationHelper,
        private RouterInterface $router,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CoreEvents::BUILD_MAILVOTECH_JS => ['onBuildJs', 254],
        ];
    }

    public function onBuildJs(BuildJsEvent $event): void
    {
        if (!$event->acceptsScope(BuildJsScope::TRACKING)) {
            return;
        }

        $integration = $this->integrationHelper->getIntegrationObject('OneSignal');

        if (!$integration || false === $integration->getIntegrationSettings()->getIsPublished()) {
            return;
        }

        $subscribeUrl   = $this->router->generate('mailvotech_notification_popup', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $subscribeTitle = 'Subscribe To Notifications';
        $width          = 450;
        $height         = 450;

        $js = <<<JS
if (window.MailVotechJS && window.MailVotechJS.runtimeReady === true) {
        
        {$this->notificationHelper->getHeaderScript()}
       
MailVotechJS.notification = {
    init: function () {
        
        {$this->notificationHelper->getScript()}
         
        var subscribeButton = document.getElementById('mailvotech-notification-subscribe');

        if (subscribeButton) {
            subscribeButton.addEventListener('click', MailVotechJS.notification.popup);
        }
    },

    popup: function () {
        var subscribeUrl = '{$subscribeUrl}';
        var subscribeTitle = '{$subscribeTitle}';
        var w = {$width};
        var h = {$height};

        // Fixes dual-screen position                         Most browsers      Firefox
        var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
        var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;

        var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
        var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

        var left = ((width / 2) - (w / 2)) + dualScreenLeft;
        var top = ((height / 2) - (h / 2)) + dualScreenTop;

        var subscribeWindow = window.open(
            subscribeUrl,
            subscribeTitle,
            'scrollbars=yes, width=' + w + ',height=' + h + ',top=' + top + ',left=' + left + ',directories=0,titlebar=0,toolbar=0,location=0,status=0,menubar=0,scrollbars=no,resizable=no'
        );

        if (window.focus) {
            subscribeWindow.focus();
        }
        
        window.closeSubscribeWindow = function() { subscribeWindow.close(); };
    }
};

MailVotechJS.documentReady(MailVotechJS.notification.init);
}
JS;

        $event->appendJsForScope($js, BuildJsScope::TRACKING, 'MailVotech Notification JS');
    }
}
