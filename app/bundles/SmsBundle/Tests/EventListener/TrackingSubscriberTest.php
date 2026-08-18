<?php

declare(strict_types=1);

namespace MailVotech\SmsBundle\Tests\EventListener;

use MailVotech\EmailBundle\Entity\Email;
use MailVotech\EmailBundle\Entity\Stat;
use MailVotech\EmailBundle\Entity\StatRepository;
use MailVotech\EmailBundle\EventListener\TrackingSubscriber;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Event\ContactIdentificationEvent;

final class TrackingSubscriberTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&StatRepository
     */
    private \PHPUnit\Framework\MockObject\MockObject $statRepository;

    protected function setUp(): void
    {
        $this->statRepository = $this->createMock(StatRepository::class);
    }

    public function testIdentifyContactByStat(): void
    {
        $ct = [
            'lead'    => 2,
            'channel' => [
                'email' => 1,
            ],
            'stat'    => 'abc123',
        ];

        $email = $this->createMock(Email::class);
        $email->method('getId')
            ->willReturn(1);

        $lead = $this->createMock(Lead::class);
        $lead->method('getId')
            ->willReturn(2);

        $stat = new Stat();
        $stat->setEmail($email);
        $stat->setLead($lead);

        $this->statRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['trackingHash' => 'abc123'])
            ->willReturn($stat);

        $event = new ContactIdentificationEvent($ct);

        $this->getSubscriber()->onIdentifyContact($event);

        $this->assertEquals($lead->getId(), $event->getIdentifiedContact()->getId());
    }

    public function testChannelMismatchDoesNotIdentify(): void
    {
        $ct = [
            'lead'    => 2,
            'channel' => [
                'sms' => 1,
            ],
            'stat'    => 'abc123',
        ];

        $event = new ContactIdentificationEvent($ct);

        $this->getSubscriber()->onIdentifyContact($event);

        $this->assertNotInstanceOf(Lead::class, $event->getIdentifiedContact());
    }

    public function testChannelIdMismatchDoesNotIdentify(): void
    {
        $ct = [
            'lead'    => 2,
            'channel' => [
                'email' => 2,
            ],
            'stat'    => 'abc123',
        ];

        $email = $this->createMock(Email::class);
        $email->method('getId')
            ->willReturn(1);

        $lead = $this->createMock(Lead::class);
        $lead->method('getId')
            ->willReturn(2);

        $stat = new Stat();
        $stat->setEmail($email);
        $stat->setLead($lead);

        $this->statRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['trackingHash' => 'abc123'])
            ->willReturn($stat);

        $event = new ContactIdentificationEvent($ct);

        $this->getSubscriber()->onIdentifyContact($event);

        $this->assertNotInstanceOf(Lead::class, $event->getIdentifiedContact());
    }

    public function testStatEmptyLeadDoesNotIdentify(): void
    {
        $ct = [
            'lead'    => 2,
            'channel' => [
                'email' => 2,
            ],
            'stat'    => 'abc123',
        ];

        $email = $this->createMock(Email::class);
        $email->method('getId')
            ->willReturn(1);

        $stat = new Stat();
        $stat->setEmail($email);

        $this->statRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['trackingHash' => 'abc123'])
            ->willReturn($stat);

        $event = new ContactIdentificationEvent($ct);

        $this->getSubscriber()->onIdentifyContact($event);

        $this->assertNotInstanceOf(Lead::class, $event->getIdentifiedContact());
    }

    private function getSubscriber(): TrackingSubscriber
    {
        return new TrackingSubscriber($this->statRepository);
    }
}
