<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Tests\Unit\EventListener;

use MailVotech\CoreBundle\Event\BuildJsEvent;
use MailVotech\CoreBundle\Event\BuildJsScope;
use MailVotech\CoreBundle\EventListener\BuildJsSubscriber;
use PHPUnit\Framework\TestCase;

final class BuildJsSubscriberTest extends TestCase
{
    public function testRuntimeIsAnonymousAndExposesThePublicRuntimeApi(): void
    {
        $event = new BuildJsEvent('', true, [BuildJsScope::RUNTIME]);

        (new BuildJsSubscriber())->onBuildJs($event);

        $js = $event->getJs();
        $this->assertStringContainsString('MailVotechJS.makeCORSRequest = function', $js);
        $this->assertStringContainsString('MailVotechJS.appendTrackedContact = function(data)', $js);
        $this->assertStringContainsString('MailVotechJS.requestWithCredentials = false', $js);
        $this->assertStringContainsString('MailVotechJS.trackingEnabled = false', $js);
        $this->assertStringContainsString('MailVotechJS.runtimeReady = true', $js);
        $this->assertStringContainsString('MailVotechJS.beforeFirstEventDelivery = function', $js);
        $this->assertStringNotContainsString("localStorage.getItem('mtc_id')", $js);
        $this->assertStringNotContainsString('MailVotechJS.getTrackedContact = function', $js);
        $this->assertStringNotContainsString('MailVotechJS.checkForTrackingPixel = function', $js);
    }

    public function testTrackingRestoresIdentityAndCredentialedRequests(): void
    {
        $event = new BuildJsEvent('', true, [BuildJsScope::TRACKING]);

        (new BuildJsSubscriber())->onBuildJs($event);

        $js = $event->getJs();
        $this->assertStringContainsString('MailVotechJS.runtimeReady !== true', $js);
        $this->assertStringContainsString('MailVotechJS.trackingEnabled = true', $js);
        $this->assertStringContainsString('MailVotechJS.requestWithCredentials = true', $js);
        $this->assertStringContainsString("localStorage.getItem('mtc_id')", $js);
        $this->assertStringContainsString('MailVotechJS.setTrackedContact = function', $js);
        $this->assertStringContainsString('MailVotechJS.checkForTrackingPixel = function', $js);
        $this->assertStringNotContainsString('MailVotechJS.serialize = function', $js);
        $this->assertStringNotContainsString('MailVotechJS.setCookie = function', $js);
    }

    public function testLegacyBuildContainsRuntimeBeforeTracking(): void
    {
        $event = new BuildJsEvent('', true);

        (new BuildJsSubscriber())->onBuildJs($event);

        $js = $event->getJs();
        $this->assertLessThan(strpos($js, 'MailVotechJS.trackingEnabled = true'), strpos($js, 'MailVotechJS.runtimeReady = true'));
    }
}
