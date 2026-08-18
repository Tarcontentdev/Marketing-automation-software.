<?php

declare(strict_types=1);

namespace MailVotech\PageBundle\Tests\Unit\EventListener;

use MailVotech\CoreBundle\Event\BuildJsEvent;
use MailVotech\CoreBundle\Event\BuildJsScope;
use MailVotech\PageBundle\EventListener\BuildJsSubscriber;
use MailVotech\PageBundle\Helper\TrackingHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;

final class BuildJsSubscriberTest extends TestCase
{
    public function testEssentialBuildSkipsAllPageTrackingContributions(): void
    {
        $trackingHelper = $this->createMock(TrackingHelper::class);
        $trackingHelper->expects($this->never())->method('getLead');
        $router = $this->createMock(RouterInterface::class);
        $router->expects($this->never())->method('generate');
        $subscriber = new BuildJsSubscriber($trackingHelper, $router);
        $event      = new BuildJsEvent('', true, [BuildJsScope::ESSENTIAL]);

        $subscriber->onBuildJsForTrackingEvent($event);
        $subscriber->onBuildJs($event);

        $this->assertSame('', $event->getJs());
    }

    public function testTrackingBuildContainsGuardedPageAndPixelContributions(): void
    {
        $trackingHelper = $this->createStub(TrackingHelper::class);
        $trackingHelper->method('getLead')->willReturn(null);
        $trackingHelper->method('displayInitCode')->willReturnCallback(
            static fn (string $service): string => match ($service) {
                'google_analytics' => 'G-TEST',
                'facebook_pixel'   => 'FB-TEST',
                default            => throw new \UnexpectedValueException($service),
            },
        );
        $trackingHelper->method('getAnonymizeIp')->willReturn(false);

        $router = $this->createStub(RouterInterface::class);
        $router->method('generate')->willReturnCallback(
            static fn (string $route): string => match ($route) {
                'mailvotech_page_tracker'            => 'https://mailvotech.example/mtracking.gif',
                'mailvotech_page_tracker_cors'       => 'https://mailvotech.example/mtc/event',
                'mailvotech_page_tracker_getcontact' => 'https://mailvotech.example/mtc/contact',
                default                          => throw new \UnexpectedValueException($route),
            },
        );

        $subscriber = new BuildJsSubscriber($trackingHelper, $router);
        $event      = new BuildJsEvent('', true, [BuildJsScope::TRACKING]);

        $subscriber->onBuildJsForTrackingEvent($event);
        $subscriber->onBuildJs($event);

        $js = $event->getJs();
        $this->assertStringContainsString('window.MailVotechJS.runtimeReady === true', $js);
        $this->assertStringContainsString('m.runtimeReady !== true', $js);
        $this->assertStringContainsString('https://www.googletagmanager.com/gtag/js?id=G-TEST', $js);
        $this->assertStringContainsString('window.gtag', $js);
        $this->assertStringContainsString('dataLayer.push(arguments)', $js);
        $this->assertStringContainsString('https://connect.facebook.net/en_US/fbevents.js', $js);
        $this->assertStringContainsString("typeof events.focus_item !== 'undefined'", $js);
        $this->assertStringContainsString("MailVotechJS.insertScript(e[i]['js']);", $js);
        $this->assertStringContainsString('m.deliverPageEvent = function', $js);
        $this->assertStringNotContainsString('MailVotechJS.serialize = function', $js);
    }
}
