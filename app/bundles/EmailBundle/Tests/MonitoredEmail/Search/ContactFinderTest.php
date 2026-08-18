<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\Tests\MonitoredEmail\Search;

use MailVotech\EmailBundle\Entity\Email;
use MailVotech\EmailBundle\Entity\Stat;
use MailVotech\EmailBundle\Entity\StatRepository;
use MailVotech\EmailBundle\MonitoredEmail\Processor\Address;
use MailVotech\EmailBundle\MonitoredEmail\Search\ContactFinder;
use MailVotech\EmailBundle\MonitoredEmail\Search\Result;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Entity\LeadRepository;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(ContactFinder::class)]
#[CoversClass(Result::class)]
#[CoversClass(Address::class)]
final class ContactFinderTest extends \PHPUnit\Framework\TestCase
{
    #[TestDox('Contact should be found via contact email address')]
    public function testContactFoundByDelegationForAddress(): void
    {
        $lead = new Lead();
        $lead->setEmail('contact@email.com');

        $statRepository = $this->createMock(StatRepository::class);
        $statRepository->expects($this->never())
            ->method('findOneBy');

        $leadRepository = $this->createMock(LeadRepository::class);
        $leadRepository->expects($this->once())
            ->method('getContactsByEmail')
            ->willReturn([$lead]);

        $logger = $this->createStub(Logger::class);

        $finder = new ContactFinder($statRepository, $leadRepository, $logger);
        $result = $finder->find($lead->getEmail(), 'contact@test.com');

        $this->assertEquals($result->getContacts(), [$lead]);
    }

    #[TestDox('Contact should be found via a hash in to email address')]
    public function testContactFoundByDelegationForHash(): void
    {
        $lead = new Lead();
        $lead->setEmail('contact@email.com');

        $stat = new Stat();
        $stat->setLead($lead);

        $statRepository = $this->createMock(StatRepository::class);
        $statRepository->expects($this->once())
            ->method('findOneBy')
            ->willReturnCallback(
                function (array $criteria) use ($stat): Stat {
                    $this->assertArrayHasKey('trackingHash', $criteria);
                    $stat->setTrackingHash($criteria['trackingHash']);

                    $email = new Email();
                    $stat->setEmail($email);

                    return $stat;
                }
            );

        $leadRepository = $this->createMock(LeadRepository::class);
        $leadRepository->expects($this->never())
            ->method('getContactsByEmail');

        $logger = $this->createStub(Logger::class);

        $finder = new ContactFinder($statRepository, $leadRepository, $logger);
        $result = $finder->find($lead->getEmail(), 'test+unsubscribe_123abc@test.com');

        $this->assertEquals($result->getStat(), $stat);
        $this->assertEquals($result->getContacts(), [$lead]);
    }
}
