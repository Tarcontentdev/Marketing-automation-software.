<?php

declare(strict_types=1);

namespace MailVotech\CampaignBundle\Tests\Membership\Action;

use MailVotech\CampaignBundle\Entity\Lead as CampaignMember;
use MailVotech\CampaignBundle\Entity\LeadEventLogRepository;
use MailVotech\CampaignBundle\Entity\LeadRepository;
use MailVotech\CampaignBundle\Membership\Action\Remover;
use MailVotech\CampaignBundle\Membership\Exception\ContactAlreadyRemovedFromCampaignException;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Twig\Helper\DateHelper;
use Symfony\Contracts\Translation\TranslatorInterface;

final class RemoverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&LeadEventLogRepository
     */
    private \PHPUnit\Framework\MockObject\MockObject $leadEventLogRepository;

    protected function setUp(): void
    {
        $this->leadEventLogRepository = $this->createMock(LeadEventLogRepository::class);
    }

    public function testMemberHasDateExitedSetWithForcedExit(): void
    {
        $campaignMember = new CampaignMember();
        $campaignMember->setManuallyRemoved(false);

        $this->leadEventLogRepository->expects($this->once())
            ->method('unscheduleEvents');

        $this->getRemover()->updateExistingMembership($campaignMember, true);

        $this->assertInstanceOf(\DateTime::class, $campaignMember->getDateLastExited());
    }

    public function testMemberHasDateExistedSetToNullWhenRemovedByFilter(): void
    {
        $campaignMember = new CampaignMember();
        $campaignMember->setManuallyRemoved(false);

        $this->leadEventLogRepository->expects($this->once())
            ->method('unscheduleEvents');

        $this->getRemover()->updateExistingMembership($campaignMember, false);

        $this->assertNotInstanceOf(\DateTimeInterface::class, $campaignMember->getDateLastExited());
    }

    public function testExceptionThrownWhenMemberIsAlreadyRemoved(): void
    {
        $this->expectException(ContactAlreadyRemovedFromCampaignException::class);

        $campaignMember = new CampaignMember();
        $campaignMember->setManuallyRemoved(true);

        $this->getRemover()->updateExistingMembership($campaignMember, false);
    }

    private function getRemover(): Remover
    {
        $translator     = $this->createMock(TranslatorInterface::class);
        $dateTimeHelper = new DateHelper(
            'Y-m-d H:i:s',
            'Y-m-d H:i',
            'Y-m-d',
            'H:i',
            $translator,
            $this->createStub(CoreParametersHelper::class)
        );

        return new Remover($this->createStub(LeadRepository::class), $this->leadEventLogRepository, $translator, $dateTimeHelper);
    }
}
