<?php

declare(strict_types=1);

namespace MailVotech\CampaignBundle\Tests\Event;

use Doctrine\Common\Collections\ArrayCollection;
use MailVotech\CampaignBundle\Entity\Event;
use MailVotech\CampaignBundle\Entity\LeadEventLog;
use MailVotech\CampaignBundle\Event\PendingEvent;
use MailVotech\CampaignBundle\EventCollector\Accessor\Event\ActionAccessor;
use MailVotech\LeadBundle\Entity\Lead;

final class PendingEventTest extends \PHPUnit\Framework\TestCase
{
    public function testFailAndPassRemainingWithError(): void
    {
        $event    = new Event();
        $contact  = new Lead();
        $logA     = new LeadEventLog();
        $logB     = new LeadEventLog();
        $interval = new \DateInterval('PT10M');

        $logA->setLead($contact); // Will fail.
        $logB->setLead($contact); // Will pass with error.

        $pendingEvent = new PendingEvent(new ActionAccessor([]), $event, new ArrayCollection([$logA, $logB]));

        $pendingEvent->fail($logA, 'reason A', $interval);
        $pendingEvent->passRemainingWithError('Error B');

        $failedLogs  = $pendingEvent->getFailures();
        $successLogs = $pendingEvent->getSuccessful();

        $this->assertCount(1, $failedLogs);
        $this->assertCount(1, $successLogs);
        $this->AssertSame($logA, $failedLogs->current());
        $this->AssertSame($logB, $successLogs->current());
        $this->AssertSame($interval, $logA->getRescheduleInterval());
        $this->AssertSame(['failed' => 1, 'reason' => 'reason A'], $logA->getMetadata());
        $this->AssertSame(['failed' => 1, 'reason' => 'Error B'], $logB->getMetadata());
        $this->assertNotInstanceOf(\DateInterval::class, $logB->getRescheduleInterval());
    }
}
