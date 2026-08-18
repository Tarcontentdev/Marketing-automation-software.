<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\EventListener;

use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Event\DoNotContactAddEvent;
use MailVotech\LeadBundle\Event\DoNotContactRemoveEvent;
use MailVotech\LeadBundle\EventListener\DoNotContactSubscriber;
use MailVotech\LeadBundle\Model\DoNotContact;

final class DoNotContactSubscriberTest extends \PHPUnit\Framework\TestCase
{
    private DoNotContactSubscriber $doNotContactSubscriber;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&DoNotContact
     */
    private \PHPUnit\Framework\MockObject\MockObject $doNotContact;

    protected function setUp(): void
    {
        $this->doNotContact               = $this->createMock(DoNotContact::class);
        $this->doNotContactSubscriber     = new DoNotContactSubscriber($this->doNotContact);
    }

    public function testGetSubscribedEvents(): void
    {
        $this->assertSame(
            [
                DoNotContactAddEvent::ADD_DONOT_CONTACT       => ['addDncForLead', 0],
                DoNotContactRemoveEvent::REMOVE_DONOT_CONTACT => ['removeDncForLead', 0],
            ],
            $this->doNotContactSubscriber->getSubscribedEvents()
        );
    }

    public function testAddDncForLeadForNewContacts(): void
    {
        $lead              = new Lead();
        $doNotContactEvent = new DoNotContactAddEvent($lead, 'email');

        $this->doNotContact->expects($this->once())->method('createDncRecord');
        $this->doNotContact->expects($this->never())->method('addDncForContact');

        $this->doNotContactSubscriber->addDncForLead($doNotContactEvent);
    }

    public function testAddDncForLeadForExistedContacts(): void
    {
        $lead = new Lead();
        $lead->setId(1);
        $doNotContactEvent = new DoNotContactAddEvent($lead, 'email');

        $this->doNotContact->expects($this->never())->method('createDncRecord');
        $this->doNotContact->expects($this->once())->method('addDncForContact');

        $this->doNotContactSubscriber->addDncForLead($doNotContactEvent);
    }
}
