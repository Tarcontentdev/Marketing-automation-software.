<?php

declare(strict_types=1);

namespace MailVotech\SmsBundle\Tests\EventListener;

use MailVotech\CoreBundle\Event\TokenReplacementEvent;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Model\AuditLogModel;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\PageBundle\Entity\Trackable;
use MailVotech\PageBundle\Helper\TokenHelper;
use MailVotech\PageBundle\Model\TrackableModel;
use MailVotech\SmsBundle\EventListener\SmsSubscriber;
use MailVotech\SmsBundle\Helper\SmsHelper;
use PHPUnit\Framework\TestCase;

final class SmsSubscriberTest extends TestCase
{
    private string $messageText = 'custom http://mailvotech.com text';

    private string $messageUrl = 'http://mailvotech.com';

    public function testOnTokenReplacementWithTrackableUrls(): void
    {
        $mockAuditLogModel = $this->createStub(AuditLogModel::class);

        $mockTrackableModel = $this->createMock(TrackableModel::class);
        $mockTrackableModel->method('parseContentForTrackables')->willReturn([
            $this->messageUrl,
            new Trackable(),
        ]);
        $mockTrackableModel->method('generateTrackableUrl')->willReturn('custom');

        $mockPageTokenHelper = $this->createMock(TokenHelper::class);
        $mockPageTokenHelper->method('findPageTokens')->willReturn([]);

        $mockAssetTokenHelper = $this->createMock(\MailVotech\AssetBundle\Helper\TokenHelper::class);
        $mockAssetTokenHelper->method('findAssetTokens')->willReturn([]);

        $mockSmsHelper = $this->createMock(SmsHelper::class);
        $mockSmsHelper->method('getDisableTrackableUrls')->willReturn(false);

        $lead                  = new Lead();
        $tokenReplacementEvent = new TokenReplacementEvent($this->messageText, $lead, ['channel' => [1 => 'sms']]);
        $subscriber            = new SmsSubscriber(
            $mockAuditLogModel,
            $mockTrackableModel,
            $mockPageTokenHelper,
            $mockAssetTokenHelper,
            $mockSmsHelper,
            $this->createStub(CoreParametersHelper::class)
        );
        $subscriber->onTokenReplacement($tokenReplacementEvent);
        $this->assertNotSame($this->messageText, $tokenReplacementEvent->getContent());
    }

    public function testOnTokenReplacementWithDisableTrackableUrls(): void
    {
        $mockAuditLogModel = $this->createStub(AuditLogModel::class);

        $mockTrackableModel = $this->createMock(TrackableModel::class);
        $mockTrackableModel->method('parseContentForTrackables')->willReturn([
            $this->messageUrl,
            new Trackable(),
        ]);
        $mockTrackableModel->method('generateTrackableUrl')->willReturn('custom');

        $mockPageTokenHelper = $this->createMock(TokenHelper::class);
        $mockPageTokenHelper->method('findPageTokens')->willReturn([]);

        $mockAssetTokenHelper = $this->createMock(\MailVotech\AssetBundle\Helper\TokenHelper::class);
        $mockAssetTokenHelper->method('findAssetTokens')->willReturn([]);

        $mockSmsHelper = $this->createMock(SmsHelper::class);
        $mockSmsHelper->method('getDisableTrackableUrls')->willReturn(true);

        $lead                  = new Lead();
        $tokenReplacementEvent = new TokenReplacementEvent($this->messageText, $lead, ['channel' => ['sms', 1]]);
        $subscriber            = new SmsSubscriber(
            $mockAuditLogModel,
            $mockTrackableModel,
            $mockPageTokenHelper,
            $mockAssetTokenHelper,
            $mockSmsHelper,
            $this->createStub(CoreParametersHelper::class)
        );
        $subscriber->onTokenReplacement($tokenReplacementEvent);
        $this->assertSame($this->messageText, $tokenReplacementEvent->getContent());
    }
}
