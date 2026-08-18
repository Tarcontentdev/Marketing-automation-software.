<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\Tests\Model;

use MailVotech\EmailBundle\Entity\Stat;
use MailVotech\EmailBundle\Model\EmailStatModel;
use MailVotech\EmailBundle\Model\TransportCallback;
use MailVotech\EmailBundle\MonitoredEmail\Search\ContactFinder;
use MailVotech\EmailBundle\MonitoredEmail\Search\Result;
use MailVotech\LeadBundle\Entity\DoNotContact as DNC;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Model\DoNotContact;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

final class TransportCallbackTest extends TestCase
{
    public function testStatSave(): void
    {
        $dncModel = new class() extends DoNotContact {
            public function __construct()
            {
            }

            public function addDncForContact($contactId, $channel, $reason = DNC::BOUNCED, ?string $comments = '', $persist = true, $checkCurrentStatus = true, $allowUnsubscribeOverride = false): bool
            {
                Assert::assertSame('email', $channel);
                Assert::assertSame(DNC::BOUNCED, $reason);

                return true;
            }
        };

        $contactFinder = new class() extends ContactFinder {
            public function __construct()
            {
            }

            public function findByHash($hash): Result
            {
                Assert::assertSame('some-hash-id', $hash);

                $result  = new Result();
                $contact = new Lead();
                $stat    = new Stat();
                $result->addContact($contact);
                $result->setStat($stat);

                return $result;
            }
        };

        $emailStatModel = new class() extends EmailStatModel {
            public function __construct()
            {
            }

            public function saveEntity(Stat $stat): void
            {
                Assert::assertTrue($stat->isFailed());
                Assert::assertArrayHasKey('bounces', $stat->getOpenDetails());
                Assert::assertArrayHasKey(0, $stat->getOpenDetails()['bounces']);
                Assert::assertArrayHasKey('datetime', $stat->getOpenDetails()['bounces'][0]);
                Assert::assertArrayHasKey('reason', $stat->getOpenDetails()['bounces'][0]);
                Assert::assertSame('some-comments', $stat->getOpenDetails()['bounces'][0]['reason']);
            }
        };

        $transportCallback = new TransportCallback($dncModel, $contactFinder, $emailStatModel);

        $transportCallback->addFailureByHashId('some-hash-id', 'some-comments');
    }
}
