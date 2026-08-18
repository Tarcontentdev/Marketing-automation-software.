<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\Event;

use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Event\LeadEvent;

final class LeadEventTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructGettersSetters(): void
    {
        $lead  = new Lead();
        $event = new LeadEvent($lead);

        $this->assertEquals($lead, $event->getLead());
        $this->assertFalse($event->isNew());

        $event = new LeadEvent($lead, false);
        $this->assertFalse($event->isNew());

        $event = new LeadEvent($lead, true);
        $this->assertTrue($event->isNew());
    }
}
