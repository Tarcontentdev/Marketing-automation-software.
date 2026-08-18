<?php

declare(strict_types=1);

namespace MailVotech\CampaignBundle\Tests\Executioner\ContactFinder;

use Doctrine\Common\Collections\ArrayCollection;
use MailVotech\CampaignBundle\Entity\CampaignRepository;
use MailVotech\CampaignBundle\Executioner\ContactFinder\KickoffContactFinder;
use MailVotech\CampaignBundle\Executioner\ContactFinder\Limiter\ContactLimiter;
use MailVotech\CampaignBundle\Executioner\Exception\NoContactsFoundException;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Entity\LeadRepository;
use Psr\Log\NullLogger;

final class KickoffContactFinderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&LeadRepository
     */
    private \PHPUnit\Framework\MockObject\MockObject $leadRepository;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&CampaignRepository
     */
    private \PHPUnit\Framework\MockObject\MockObject $campaignRepository;

    protected function setUp(): void
    {
        $this->leadRepository = $this->createMock(LeadRepository::class);

        $this->campaignRepository = $this->createMock(CampaignRepository::class);
    }

    public function testNoContactsFoundExceptionIsThrown(): void
    {
        $this->campaignRepository->expects($this->once())
            ->method('getPendingContactIds')
            ->willReturn([]);

        $this->expectException(NoContactsFoundException::class);

        $limiter = new ContactLimiter(0, 0, 0, 0);
        $this->getContactFinder()->getContacts(1, $limiter);
    }

    public function testNoContactsFoundExceptionIsThrownIfEntitiesAreNotFound(): void
    {
        $contactIds = [1, 2];

        $this->campaignRepository->expects($this->once())
            ->method('getPendingContactIds')
            ->willReturn($contactIds);

        $this->leadRepository->expects($this->once())
            ->method('getContactCollection')
            ->willReturn(new ArrayCollection([]));

        $this->expectException(NoContactsFoundException::class);

        $limiter = new ContactLimiter(0, 0, 0, 0);
        $this->getContactFinder()->getContacts(1, $limiter);
    }

    public function testArrayCollectionIsReturnedForFoundContacts(): void
    {
        $contactIds = [1, 2];

        $this->campaignRepository->expects($this->once())
            ->method('getPendingContactIds')
            ->willReturn($contactIds);

        $foundContacts = new ArrayCollection([new Lead(), new Lead()]);
        $this->leadRepository->expects($this->once())
            ->method('getContactCollection')
            ->willReturn($foundContacts);

        $limiter = new ContactLimiter(0, 0, 0, 0);
        $this->assertEquals($foundContacts, $this->getContactFinder()->getContacts(1, $limiter));
    }

    private function getContactFinder(): KickoffContactFinder
    {
        return new KickoffContactFinder(
            $this->leadRepository,
            $this->campaignRepository,
            new NullLogger()
        );
    }
}
