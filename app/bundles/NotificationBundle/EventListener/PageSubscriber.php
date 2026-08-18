<?php

namespace MailVotech\NotificationBundle\EventListener;

use MailVotech\CoreBundle\Twig\Helper\AssetsHelper;
use MailVotech\PageBundle\Event\PageDisplayEvent;
use MailVotech\PageBundle\PageEvents;
use MailVotech\PluginBundle\Helper\IntegrationHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class PageSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private AssetsHelper $assetsHelper,
        private IntegrationHelper $integrationHelper,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PageEvents::PAGE_ON_DISPLAY => ['onPageDisplay', 0],
        ];
    }

    public function onPageDisplay(PageDisplayEvent $event): void
    {
        $integrationObject = $this->integrationHelper->getIntegrationObject('OneSignal');
        $settings          = $integrationObject->getIntegrationSettings();
        $features          = $settings->getFeatureSettings();

        $script = '';
        if (!in_array('landing_page_enabled', $features)) {
            $script = 'disable_notification = true;';
        }

        $this->assetsHelper->addScriptDeclaration($script, 'onPageDisplay_headClose');
    }
}
