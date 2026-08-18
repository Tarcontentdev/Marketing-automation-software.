<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\Event;

use MailVotech\LeadBundle\Entity\Company;
use MailVotech\LeadBundle\Event\CompanyMergeEvent;
use PHPUnit\Framework\TestCase;

final class CompanyMergeEventTest extends TestCase
{
    public function testConstructGettersSetters(): void
    {
        $victor  = new Company();
        $loser   = new Company();
        $event   = new CompanyMergeEvent($victor, $loser);

        $this->assertEquals($victor, $event->getVictor());
        $this->assertEquals($loser, $event->getLoser());
    }
}
