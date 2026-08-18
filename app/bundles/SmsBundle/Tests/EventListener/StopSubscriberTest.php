<?php

declare(strict_types=1);

namespace MailVotech\SmsBundle\Tests\EventListener;

use MailVotech\LeadBundle\Entity\DoNotContact;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Model\DoNotContact as DoNotContactModel;
use MailVotech\SmsBundle\Event\ReplyEvent;
use MailVotech\SmsBundle\EventListener\StopSubscriber;

final class StopSubscriberTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&DoNotContactModel
     */
    private \PHPUnit\Framework\MockObject\MockObject $doNotContactModel;

    protected function setUp(): void
    {
        $this->doNotContactModel = $this->createMock(DoNotContactModel::class);
    }

    public function testLeadAddedToDNC(): void
    {
        $lead = new Lead();
        $lead->setId(1);
        $event = new ReplyEvent($lead, 'stop');

        $this->doNotContactModel->expects($this->once())
        ->method('addDncForContact')
        ->with(1, 'sms', DoNotContact::UNSUBSCRIBED);

        $this->StopSubscriber()->onReply($event);
    }

    private function StopSubscriber(): StopSubscriber
    {
        return new StopSubscriber($this->doNotContactModel);
    }
}
