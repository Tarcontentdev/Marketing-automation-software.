<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\Tests\EventListener;

use Doctrine\Common\Collections\ArrayCollection;
use MailVotech\CampaignBundle\Entity\Event;
use MailVotech\CampaignBundle\Entity\LeadEventLog;
use MailVotech\CampaignBundle\Event\PendingEvent;
use MailVotech\CampaignBundle\EventCollector\Accessor\Event\ActionAccessor;
use MailVotech\CampaignBundle\Executioner\RealTimeExecutioner;
use MailVotech\EmailBundle\Entity\StatRepository;
use MailVotech\EmailBundle\EventListener\CampaignSubscriber;
use MailVotech\EmailBundle\Exception\EmailCouldNotBeSentException;
use MailVotech\EmailBundle\Model\EmailModel;
use MailVotech\EmailBundle\Model\SendEmailToUser;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Model\LeadModel;
use Symfony\Contracts\Translation\TranslatorInterface;

final class CampaignSubscriberTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&SendEmailToUser
     */
    private \PHPUnit\Framework\MockObject\MockObject $sendEmailToUser;

    private CampaignSubscriber $subscriber;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sendEmailToUser     = $this->createMock(SendEmailToUser::class);

        $this->subscriber = new CampaignSubscriber(
            $this->createStub(EmailModel::class),
            $this->createStub(RealTimeExecutioner::class),
            $this->sendEmailToUser,
            $this->createStub(TranslatorInterface::class),
            $this->createStub(LeadModel::class),
            $this->createStub(StatRepository::class)
        );
    }

    public function testOnCampaignTriggerActionSendEmailToUserWithWrongEventType(): void
    {
        $eventAccessor = $this->createStub(ActionAccessor::class);
        $event         = new Event();
        $lead          = (new Lead())->setEmail('tester@mailvotech.org');

        $event->setType(Event::TYPE_ACTION);

        $leadEventLog = $this->createMock(LeadEventLog::class);
        $leadEventLog
            ->method('getLead')
            ->willReturn($lead);
        $leadEventLog
            ->method('getId')
            ->willReturn(6);

        $logs = new ArrayCollection([$leadEventLog]);

        $pendingEvent = new PendingEvent($eventAccessor, $event, $logs);
        $this->subscriber->onCampaignTriggerActionSendEmailToUser($pendingEvent);

        $this->assertCount(0, $pendingEvent->getSuccessful());
        $this->assertCount(0, $pendingEvent->getFailures());
    }

    public function testOnCampaignTriggerActionSendEmailToUserWithSendingTheEmail(): void
    {
        $eventAccessor = $this->createStub(ActionAccessor::class);
        $event         = (new Event())->setType('email.send.to.user');
        $lead          = (new Lead())->setEmail('tester@mailvotech.org');

        $leadEventLog = $this->createMock(LeadEventLog::class);
        $leadEventLog
            ->method('getLead')
            ->willReturn($lead);
        $leadEventLog
            ->method('getId')
            ->willReturn(0);
        $leadEventLog
            ->method('setIsScheduled')
            ->with(false)
            ->willReturn($leadEventLog);

        $logs = new ArrayCollection([$leadEventLog]);

        $pendingEvent = new PendingEvent($eventAccessor, $event, $logs);
        $this->subscriber->onCampaignTriggerActionSendEmailToUser($pendingEvent);

        $this->assertCount(1, $pendingEvent->getSuccessful());
        $this->assertCount(0, $pendingEvent->getFailures());
    }

    public function testOnCampaignTriggerActionSendEmailToUserWithError(): void
    {
        $eventAccessor = $this->createStub(ActionAccessor::class);
        $event         = (new Event())->setType('email.send.to.user');
        $lead          = (new Lead())->setEmail('tester@mailvotech.org');

        $leadEventLog = $this->createMock(LeadEventLog::class);
        $leadEventLog
            ->method('getLead')
            ->willReturn($lead);
        $leadEventLog
            ->method('getId')
            ->willReturn(0);
        $leadEventLog
            ->method('setIsScheduled')
            ->with(false)
            ->willReturn($leadEventLog);
        $leadEventLog
            ->method('getMetadata')
            ->willReturn([]);

        $logs = new ArrayCollection([$leadEventLog]);

        $this->sendEmailToUser->expects($this->once())
            ->method('sendEmailToUsers')
            ->with([], $lead)
            ->willThrowException(new EmailCouldNotBeSentException('Something happened'));

        $pendingEvent = new PendingEvent($eventAccessor, $event, $logs);
        $this->subscriber->onCampaignTriggerActionSendEmailToUser($pendingEvent);

        $this->assertCount(0, $pendingEvent->getSuccessful());

        $failures = $pendingEvent->getFailures();
        $this->assertCount(1, $failures);
        /** @var LeadEventLog $failure */
        $failure    = $failures->first();
        $failedLead = $failure->getLead();
        $this->assertInstanceOf(Lead::class, $failedLead);

        $this->assertSame('tester@mailvotech.org', $failedLead->getEmail());
    }

    /**
     * @throws \MailVotech\CampaignBundle\Executioner\Exception\NoContactsFoundException
     * @throws \Doctrine\ORM\ORMException
     */
    public function testOnCampaignTriggerActionSendEmailToContactWithWrongEventType(): void
    {
        $eventAccessor = $this->createStub(ActionAccessor::class);
        $event         = new Event();
        $lead          = (new Lead())->setEmail('tester@mailvotech.org');

        $leadEventLog = $this->createMock(LeadEventLog::class);
        $leadEventLog
            ->method('getLead')
            ->willReturn($lead);
        $leadEventLog
            ->method('getId')
            ->willReturn(6);

        $logs = new ArrayCollection([$leadEventLog]);

        $pendingEvent = new PendingEvent($eventAccessor, $event, $logs);
        $this->subscriber->onCampaignTriggerActionSendEmailToContact($pendingEvent);

        $this->assertCount(0, $pendingEvent->getSuccessful());
        $this->assertCount(0, $pendingEvent->getFailures());
    }
}
