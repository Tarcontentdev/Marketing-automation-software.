<?php

declare(strict_types=1);

namespace MailVotech\CampaignBundle\Tests\Executioner\ContactFinder;

use Doctrine\Common\Collections\ArrayCollection;
use MailVotech\CampaignBundle\Entity\Event;
use MailVotech\CampaignBundle\Entity\LeadRepository as CampaignLeadRepository;
use MailVotech\CampaignBundle\Executioner\ContactFinder\InactiveContactFinder;
use MailVotech\CampaignBundle\Executioner\ContactFinder\Limiter\ContactLimiter;
use MailVotech\CampaignBundle\Executioner\Exception\NoContactsFoundException;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Entity\LeadRepository;
use Psr\Log\NullLogger;

final class InactiveContactFinderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&LeadRepository
     */
    private \PHPUnit\Framework\MockObject\MockObject $leadRepository;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&CampaignLeadRepository
     */
    private \PHPUnit\Framework\MockObject\MockObject $campaignLeadRepository;

    protected function setUp(): void
    {
        $this->leadRepository         = $this->createMock(LeadRepository::class);
        $this->campaignLeadRepository = $this->createMock(CampaignLeadRepository::class);
    }

    public function testNoContactsFoundExceptionIsThrown(): void
    {
        $this->campaignLeadRepository->expects($this->once())
            ->method('getInactiveContacts')
            ->willReturn([]);

        $this->expectException(NoContactsFoundException::class);

        $limiter = new ContactLimiter(0, 0, 0, 0);
        $this->getContactFinder()->getContacts(1, new Event(), $limiter);
    }

    public function testNoContactsFoundExceptionIsThrownIfEntitiesAreNotFound(): void
    {
        $contactMemberDates = [
            1 => new \DateTime(),
        ];

        $this->campaignLeadRepository->expects($this->once())
            ->method('getInactiveContacts')
            ->willReturn($contactMemberDates);

        $this->leadRepository->expects($this->once())
            ->method('getContactCollection')
            ->willReturn(new ArrayCollection([]));

        $this->expectException(NoContactsFoundException::class);

        $limiter = new ContactLimiter(0, 0, 0, 0);
        $this->getContactFinder()->getContacts(1, new Event(), $limiter);
    }

    public function testContactsAreFoundAndStoredInCampaignMemberDatesAdded(): void
    {
        $contactMemberDates = [
            1 => new \DateTime(),
        ];

        $this->campaignLeadRepository->expects($this->once())
            ->method('getInactiveContacts')
            ->willReturn($contactMemberDates);

        $this->leadRepository->expects($this->once())
            ->method('getContactCollection')
            ->willReturn(new ArrayCollection([new Lead()]));

        $contactFinder = $this->getContactFinder();

        $limiter  = new ContactLimiter(0, 0, 0, 0);
        $contacts = $contactFinder->getContacts(1, new Event(), $limiter);
        $this->assertCount(1, $contacts);

        $this->assertEquals($contactMemberDates, $contactFinder->getDatesAdded());
    }

    private function getContactFinder(): InactiveContactFinder
    {
        return new InactiveContactFinder(
            $this->leadRepository,
            $this->campaignLeadRepository,
            new NullLogger()
        );
    }
}
