<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\Tests\EventListener;

use MailVotech\EmailBundle\EventListener\EmailToUserSubscriber;
use MailVotech\EmailBundle\Exception\EmailCouldNotBeSentException;
use MailVotech\EmailBundle\Model\SendEmailToUser;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\PointBundle\Entity\TriggerEvent;
use MailVotech\PointBundle\Event\TriggerExecutedEvent;

final class EmailToUserSubscriberTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var array<string, mixed>
     */
    private array $config = [
        'useremail' => [
            'email' => 33,
        ],
        'user_id'  => [6, 7],
        'to_owner' => true,
        'to'       => 'hello@there.com, bob@bobek.cz',
        'bcc'      => 'hidden@translation.in',
    ];

    public function testOnCampaignTriggerActionSendEmailToUserWithSendingTheEmail(): void
    {
        $lead = new Lead();

        $mockSendEmailToUser = $this->createMock(SendEmailToUser::class);

        $subscriber = new EmailToUserSubscriber($mockSendEmailToUser);

        $mockSendEmailToUser->expects($this->once())
            ->method('sendEmailToUsers')
            ->with($this->config, $lead);

        $mockSendEmailToUser->expects($this->once())
            ->method('sendEmailToUsers')
            ->with($this->config, $lead);

        $triggerEvent = new TriggerEvent();
        $triggerEvent->setProperties($this->config);

        $event = new TriggerExecutedEvent($triggerEvent, $lead);

        $subscriber->onEmailToUser($event);

        $this->assertTrue($event->getResult());
    }

    public function testOnCampaignTriggerActionSendEmailToUserWithError(): void
    {
        $lead = new Lead();

        $mockSendEmailToUser = $this->createMock(SendEmailToUser::class);

        $subscriber = new EmailToUserSubscriber($mockSendEmailToUser);

        $mockSendEmailToUser->expects($this->once())
            ->method('sendEmailToUsers')
            ->with($this->config, $lead);

        $mockSendEmailToUser->expects($this->once())
            ->method('sendEmailToUsers')
            ->with($this->config, $lead)
            ->willThrowException(new EmailCouldNotBeSentException());

        $triggerEvent = new TriggerEvent();
        $triggerEvent->setProperties($this->config);

        $event = new TriggerExecutedEvent($triggerEvent, $lead);

        $subscriber->onEmailToUser($event);

        $this->assertFalse($event->getResult());
    }
}
