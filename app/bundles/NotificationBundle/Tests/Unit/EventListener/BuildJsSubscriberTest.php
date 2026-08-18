<?php

declare(strict_types=1);

namespace MailVotech\NotificationBundle\Tests\Unit\EventListener;

use MailVotech\CoreBundle\Event\BuildJsEvent;
use MailVotech\CoreBundle\Event\BuildJsScope;
use MailVotech\NotificationBundle\EventListener\BuildJsSubscriber;
use MailVotech\NotificationBundle\Helper\NotificationHelper;
use MailVotech\PluginBundle\Entity\Integration;
use MailVotech\PluginBundle\Helper\IntegrationHelper;
use MailVotech\PluginBundle\Integration\AbstractIntegration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;

final class BuildJsSubscriberTest extends TestCase
{
    public function testEssentialBuildSkipsOneSignalLookup(): void
    {
        $integrationHelper = $this->createMock(IntegrationHelper::class);
        $integrationHelper->expects($this->never())->method('getIntegrationObject');
        $subscriber = new BuildJsSubscriber(
            $this->createStub(NotificationHelper::class),
            $integrationHelper,
            $this->createStub(RouterInterface::class),
        );
        $event = new BuildJsEvent('', true, [BuildJsScope::ESSENTIAL]);

        $subscriber->onBuildJs($event);

        $this->assertSame('', $event->getJs());
    }

    public function testTrackingBuildContainsGuardedOneSignalContribution(): void
    {
        $settings = $this->createStub(Integration::class);
        $settings->method('getIsPublished')->willReturn(true);
        $integration = $this->createStub(AbstractIntegration::class);
        $integration->method('getIntegrationSettings')->willReturn($settings);

        $integrationHelper = $this->createStub(IntegrationHelper::class);
        $integrationHelper->method('getIntegrationObject')->willReturn($integration);

        $notificationHelper = $this->createStub(NotificationHelper::class);
        $notificationHelper->method('getHeaderScript')->willReturn("MailVotechJS.insertScript('https://cdn.onesignal.com/OneSignalSDK.js');");
        $notificationHelper->method('getScript')->willReturn("MailVotechJS.makeCORSRequest('GET', '/notification/subscribe', []);");

        $router = $this->createStub(RouterInterface::class);
        $router->method('generate')->willReturn('https://mailvotech.example/notification');

        $subscriber = new BuildJsSubscriber($notificationHelper, $integrationHelper, $router);
        $event      = new BuildJsEvent('', true, [BuildJsScope::TRACKING]);

        $subscriber->onBuildJs($event);

        $js = $event->getJs();
        $this->assertStringContainsString('window.MailVotechJS.runtimeReady === true', $js);
        $this->assertStringContainsString('https://cdn.onesignal.com/OneSignalSDK.js', $js);
        $this->assertStringContainsString("MailVotechJS.makeCORSRequest('GET', '/notification/subscribe', []);", $js);
        $this->assertStringContainsString('MailVotechJS.notification = {', $js);
    }
}
