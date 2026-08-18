<?php

declare(strict_types=1);

namespace MailVotech\CampaignBundle\Tests\Executioner\Result;

use MailVotech\CampaignBundle\Executioner\Result\EvaluatedContacts;
use MailVotech\LeadBundle\Entity\Lead;

final class EvalutatedContactsTest extends \PHPUnit\Framework\TestCase
{
    public function testPassFail(): void
    {
        $evaluatedContacts = new EvaluatedContacts();
        $passLead          = new Lead();
        $evaluatedContacts->pass($passLead);

        $failedLead = new Lead();
        $evaluatedContacts->fail($failedLead);

        $passed = $evaluatedContacts->getPassed();
        $failed = $evaluatedContacts->getFailed();

        $this->assertCount(1, $passed);
        $this->assertCount(1, $failed);

        $this->assertSame($passed->first(), $passLead);
        $this->assertSame($failed->first(), $failedLead);
    }
}
