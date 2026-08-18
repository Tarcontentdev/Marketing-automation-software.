<?php

declare(strict_types=1);

namespace MailVotech\DynamicContentBundle\Tests\Unit\EventListener;

use MailVotech\CoreBundle\Event\BuildJsEvent;
use MailVotech\CoreBundle\Event\BuildJsScope;
use MailVotech\CoreBundle\Helper\PathsHelper;
use MailVotech\CoreBundle\Twig\Helper\AssetsHelper;
use MailVotech\DynamicContentBundle\EventListener\BuildJsSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class BuildJsSubscriberTest extends TestCase
{
    private BuildJsSubscriber $subscriber;

    protected function setUp(): void
    {
        $pathsHelper = $this->createStub(PathsHelper::class);
        $pathsHelper->method('getSystemPath')->willReturn('');

        $assetsHelper = new AssetsHelper($this->createStub(Packages::class));
        $assetsHelper->setPathsHelper($pathsHelper);
        $assetsHelper->setSiteUrl('https://mailvotech.example');

        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('trans')->willReturn('Please wait');

        $requestStack = new RequestStack([Request::create('https://mailvotech.example')]);

        $router = $this->createStub(RouterInterface::class);
        $router->method('generate')->willReturn('https://mailvotech.example/dwc/slotNamePlaceholder');

        $this->subscriber = new BuildJsSubscriber($assetsHelper, $translator, $requestStack, $router);
    }

    public function testEssentialBuildRegistersDormantReplacementAndEnhancementHelpers(): void
    {
        $event = new BuildJsEvent('', true, [BuildJsScope::ESSENTIAL]);

        $this->subscriber->onBuildJs($event);

        $js = $event->getJs();
        $this->assertStringContainsString('MailVotechJS.replaceDynamicContent = function', $js);
        $this->assertStringContainsString('MailVotechJS.enhanceDynamicContent = function', $js);
        $this->assertStringContainsString('MailVotechJS.initializeForms = function', $js);
        $this->assertStringContainsString("document.querySelectorAll('.mailvotech-slot form[data-mailvotech-form]", $js);
        $this->assertStringContainsString("document.getElementById('mailvotechform_' + formId + '_messenger')", $js);
        $this->assertStringContainsString('MailVotechJS.beforeFirstEventDelivery(MailVotechJS.replaceDynamicContent);', $js);
        $this->assertStringContainsString('media/js/mailvotech-form.js', $js);
        $this->assertStringContainsString("typeof MailVotechSDKLoaded == 'undefined'", $js);
        $this->assertStringContainsString('MailVotechSDK.onLoad();', $js);
        $this->assertStringContainsString('search("/focus/")', $js);
        $this->assertStringNotContainsString('MailVotechJS.setTrackedContact(response)', $js);
        $this->assertSame(2, substr_count($js, 'MailVotechJS.replaceDynamicContent'));
    }

    public function testTrackingBuildOnlyAddsIdentityResponseHook(): void
    {
        $event = new BuildJsEvent('', true, [BuildJsScope::TRACKING]);

        $this->subscriber->onBuildJs($event);

        $js = $event->getJs();
        $this->assertStringContainsString('MailVotechJS.runtimeReady !== true', $js);
        $this->assertStringContainsString('MailVotechJS.onDynamicContentResponse = function(response)', $js);
        $this->assertStringContainsString('MailVotechJS.setTrackedContact(response)', $js);
        $this->assertStringNotContainsString('MailVotechJS.replaceDynamicContent = function', $js);
        $this->assertStringNotContainsString('mailvotech-form.js', $js);
        $this->assertStringNotContainsString('search("/focus/")', $js);
    }

    public function testLegacyBuildHasOneReplacementAndOneEnhancementPass(): void
    {
        $event = new BuildJsEvent('', true);

        $this->subscriber->onBuildJs($event);

        $js = $event->getJs();
        $this->assertSame(1, substr_count($js, 'MailVotechJS.replaceDynamicContent = function'));
        $this->assertSame(1, substr_count($js, 'MailVotechJS.beforeFirstEventDelivery(MailVotechJS.replaceDynamicContent);'));
        $this->assertSame(1, substr_count($js, "MailVotechJS.makeCORSRequest('GET', url"));
        $this->assertSame(1, substr_count($js, 'MailVotechJS.enhanceDynamicContent(dwcContent);'));
        $this->assertLessThan(strpos($js, 'MailVotechJS.setTrackedContact(response)'), strpos($js, 'MailVotechJS.replaceDynamicContent = function'));
    }
}
