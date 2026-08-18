<?php

declare(strict_types=1);

namespace MailVotech\CampaignBundle\Tests;

use Doctrine\ORM\EntityManager;
use MailVotech\CampaignBundle\Entity\CampaignRepository;
use MailVotech\CampaignBundle\Entity\EventRepository;
use MailVotech\CampaignBundle\Entity\LeadEventLogRepository;
use MailVotech\CampaignBundle\Entity\LeadRepository;
use MailVotech\CampaignBundle\EventCollector\EventCollector;
use MailVotech\CampaignBundle\Membership\MembershipBuilder;
use MailVotech\CampaignBundle\Model\CampaignModel;
use MailVotech\CoreBundle\Doctrine\Provider\GeneratedColumnsProviderInterface;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Helper\UserHelper;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\CoreBundle\Translation\Translator;
use MailVotech\EmailBundle\Entity\StatRepository;
use MailVotech\FormBundle\Entity\FormRepository;
use MailVotech\FormBundle\Model\FormModel;
use MailVotech\LeadBundle\Model\ListModel;
use MailVotech\LeadBundle\Tracker\ContactTracker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class CampaignTestAbstract extends TestCase
{
    protected static int $mockId       = 232;

    protected static string $mockName  = 'Mock name';

    protected static string $mockAlias = 'Mock alias';

    /**
     * @var EntityManager&MockObject
     */
    protected ?MockObject $entityManager = null;

    protected function initCampaignModel(): CampaignModel
    {
        $entityManager       = $this->createMock(EntityManager::class);
        $this->entityManager = $entityManager;

        $security = $this->createMock(CorePermissions::class);

        $security
            ->method('isGranted')
            ->willReturn(true);

        $formRepository = $this->createMock(FormRepository::class);

        $formRepository
            ->method('getFormList')
            ->willReturn([['id' => self::$mockId, 'name' => self::$mockName]]);

        $leadListModel = $this->getMockBuilder(ListModel::class)
            ->disableOriginalConstructor()
            ->setConstructorArgs([6 => $entityManager])
            ->getMock();

        $leadListModel
            ->method('getUserLists')
            ->willReturn([['id' => self::$mockId, 'name' => self::$mockName, 'alias' => self::$mockAlias]]);

        $formModel = $this->getMockBuilder(FormModel::class)
            ->disableOriginalConstructor()
            ->setConstructorArgs([12 => $entityManager])
            ->getMock();

        return new CampaignModel(
            $leadListModel,
            $formModel,
            $this->createStub(EventCollector::class),
            $this->createStub(MembershipBuilder::class),
            $this->createStub(ContactTracker::class),
            $this->createStub(GeneratedColumnsProviderInterface::class),
            $entityManager,
            $security,
            $this->createStub(EventDispatcherInterface::class),
            $this->createStub(UrlGeneratorInterface::class),
            $this->createStub(Translator::class),
            $this->createStub(UserHelper::class),
            $this->createStub(LoggerInterface::class),
            $this->createStub(CoreParametersHelper::class),
            $this->createStub(CampaignRepository::class), // $campaignRepository
            $this->createStub(EventRepository::class), // $eventRepository
            $this->createStub(LeadRepository::class), // $leadRepository
            $this->createStub(LeadEventLogRepository::class), // $leadEventLogRepository
            $this->createStub(StatRepository::class), // $statRepository
            $formRepository, // $formRepository
        );
    }
}
