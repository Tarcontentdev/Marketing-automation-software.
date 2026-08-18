<?php

declare(strict_types=1);

namespace MailVotech\SmsBundle\Tests\EventListener;

use Doctrine\Common\Collections\ArrayCollection;
use MailVotech\CampaignBundle\Entity\Campaign;
use MailVotech\CampaignBundle\Entity\Event;
use MailVotech\CampaignBundle\Entity\LeadEventLog;
use MailVotech\CampaignBundle\Event\PendingEvent;
use MailVotech\CampaignBundle\EventCollector\Accessor\Event\ActionAccessor;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\SmsBundle\Entity\Sms;
use MailVotech\SmsBundle\EventListener\CampaignSendSubscriber;
use MailVotech\SmsBundle\Model\SmsModel;
use MailVotech\SmsBundle\Sms\TransportChain;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\Translation\TranslatorInterface;

final class CampaignSendSubscriberTest extends \PHPUnit\Framework\TestCase
{
    private MockObject&SmsModel $smsModel;

    private \PHPUnit\Framework\MockObject\Stub&TransportChain $transportChain;

    private MockObject&TranslatorInterface $translator;

    private CampaignSendSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->smsModel       = $this->createMock(SmsModel::class);
        $this->transportChain = $this->createStub(TransportChain::class);
        $this->translator     = $this->createMock(TranslatorInterface::class);
        $this->subscriber     = new CampaignSendSubscriber($this->smsModel, $this->transportChain, $this->translator);
    }

    public function testSendDeletedSms(): void
    {
        $this->smsModel->expects($this->once())->method('getEntity')->willReturn(null);
        $this->translator->method('trans')->willReturn('mailvotech.sms.campaign.failed.missing_entity');

        $event    = new Event();
        $campaign = new class() extends Campaign {
            public function getId(): int
            {
                return 111;
            }
        };
        $contact = new Lead();
        $leadLog = new class() extends LeadEventLog {
            public function getId(): int
            {
                return 456;
            }
        };

        $contact->setId(1);
        $leadLog->setLead($contact);
        $event->setProperties(['sms' => 1]);
        $event->setCampaign($campaign);
        $event->setType('sms.send_text_sms');

        $pendingEvent = new PendingEvent(new ActionAccessor([]), $event, new ArrayCollection([$leadLog->getId() => $leadLog]));

        $this->subscriber->onCampaignTriggerBatchAction($pendingEvent);
        $this->assertCount(0, $pendingEvent->getFailures());
        $this->assertCount(1, $pendingEvent->getSuccessful());
        $this->assertSame(1, $leadLog->getMetadata()['failed']);
        $this->assertSame('mailvotech.sms.campaign.failed.missing_entity', $leadLog->getMetadata()['reason']);
    }

    public function testSendUnpublishedSms(): void
    {
        $sms      = new Sms();
        $event    = new Event();
        $contact  = new Lead();
        $leadLog  = new class() extends LeadEventLog {
            public function getId(): int
            {
                return 456;
            }
        };

        $campaign  = new class() extends Campaign {
            public function getId(): int
            {
                return 111;
            }
        };
        $contact->setId(1);
        $leadLog->setLead($contact);
        $sms->setIsPublished(false);
        $event->setProperties(['sms' => 1]);
        $event->setCampaign($campaign);
        $event->setType('sms.send_text_sms');

        $this->smsModel->expects($this->once())->method('getEntity')->willReturn($sms);
        $this->translator->method('trans')->willReturn('mailvotech.sms.campaign.failed.unpublished');

        $pendingEvent = new PendingEvent(new ActionAccessor([]), $event, new ArrayCollection([$leadLog->getId() => $leadLog]));

        $this->subscriber->onCampaignTriggerBatchAction($pendingEvent);

        $this->assertCount(0, $pendingEvent->getFailures());
        $this->assertCount(1, $pendingEvent->getSuccessful());
        $this->assertSame(1, $leadLog->getMetadata()['failed']);
        $this->assertSame('mailvotech.sms.campaign.failed.unpublished', $leadLog->getMetadata()['reason']);
    }

    public function testOnCampaignTriggerBatchAction(): void
    {
        $sms = $this->createMock(Sms::class);
        $sms
            ->method('getId')
            ->willReturn(1);
        $sms
            ->method('isPublished')
            ->willReturn(true);

        // Partial mock, mocks just getRepository
        $smsModel = $this->getMockBuilder(SmsModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['sendSms', 'getEntity'])
            ->getMock();

        $smsModel->method('sendSms')->willReturn([456 => ['status' => true]]);
        $smsModel->method('getEntity')->willReturn($sms);

        $event     = new Event();
        $campaign  = new class() extends Campaign {
            public function getId(): int
            {
                return 111;
            }
        };
        $contact  = new Lead();
        $leadLog  = new class() extends LeadEventLog {
            public function getId(): int
            {
                return 456;
            }
        };

        $leadLog->setLead($contact);
        $contact->setId(789);

        $this->translator->method('trans')
            ->willReturn('random string');

        $subscriber = new CampaignSendSubscriber(
            $smsModel,
            $this->transportChain,
            $this->translator
        );

        $event->setProperties(['sms' => 1]);
        $event->setCampaign($campaign);

        $pendingEvent = new PendingEvent(new ActionAccessor([]), $event, new ArrayCollection([$leadLog->getId() => $leadLog]));

        $this->assertCount(1, $pendingEvent->getContacts());
        $subscriber->onCampaignTriggerBatchAction($pendingEvent);
    }
}
