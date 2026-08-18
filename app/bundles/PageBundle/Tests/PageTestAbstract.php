<?php

declare(strict_types=1);

namespace MailVotech\PageBundle\Tests;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use MailVotech\CoreBundle\Helper\CookieHelper;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Helper\IpLookupHelper;
use MailVotech\CoreBundle\Helper\UserHelper;
use MailVotech\CoreBundle\Model\AbTest\VariantConverterService;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\CoreBundle\Shortener\Shortener;
use MailVotech\CoreBundle\Translation\Translator;
use MailVotech\EmailBundle\Entity\EmailRepository;
use MailVotech\EmailBundle\Entity\StatRepository;
use MailVotech\EmailBundle\Helper\BotRatioHelper;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Entity\LeadRepository;
use MailVotech\LeadBundle\Entity\UtmTagRepository;
use MailVotech\LeadBundle\Helper\ContactRequestHelper;
use MailVotech\LeadBundle\Model\CompanyModel;
use MailVotech\LeadBundle\Model\FieldModel;
use MailVotech\LeadBundle\Model\LeadModel;
use MailVotech\LeadBundle\Tracker\ContactTracker;
use MailVotech\LeadBundle\Tracker\DeviceTracker;
use MailVotech\PageBundle\Entity\Hit;
use MailVotech\PageBundle\Entity\HitRepository;
use MailVotech\PageBundle\Entity\Page;
use MailVotech\PageBundle\Entity\PageRepository;
use MailVotech\PageBundle\Entity\Redirect;
use MailVotech\PageBundle\Entity\RedirectRepository;
use MailVotech\PageBundle\Entity\TrackableRepository;
use MailVotech\PageBundle\Model\PageModel;
use MailVotech\PageBundle\Model\RedirectModel;
use MailVotech\PageBundle\Model\TrackableModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class PageTestAbstract extends TestCase
{
    protected static $mockId   = 123;

    protected static $mockName = 'Mock test name';

    protected string $mockTrackingId;

    /**
     * @var Router|MockObject
     */
    protected ?MockObject $router = null;

    protected CorePermissions&MockObject $security;

    protected IpLookupHelper&MockObject $ipLookupHelper;

    protected ContactRequestHelper&MockObject $contactRequestHelper;

    protected CompanyModel&MockObject $companyModel;

    protected function setUp(): void
    {
        $this->mockTrackingId = hash('sha1', uniqid((string) mt_rand(), true));
    }

    protected function getPageModel(bool $transliterationEnabled = true, bool $validatePageHitRequiredData = true): PageModel
    {
        $this->router = $this->createMock(Router::class);

        $this->ipLookupHelper = $this->createMock(IpLookupHelper::class);
        $this->ipLookupHelper->method('isRequestTrackable')->willReturn(true);

        $redirectModel = $this->getRedirectModel();

        $this->companyModel = $this->createMock(CompanyModel::class);

        $entityManager = $this->createMock(EntityManager::class);

        $pageRepository = $this->createMock(PageRepository::class);

        $coreParametersHelper = $this->createMock(CoreParametersHelper::class);

        $hitRepository = $this->createMock(HitRepository::class);

        $contactTracker = $this->createMock(ContactTracker::class);

        $this->contactRequestHelper = $this->createMock(ContactRequestHelper::class);

        $lead = new Lead();
        $lead->setId(self::$mockId);
        $lead->setFirstname(self::$mockName);

        $contactTracker
            ->method('getContact')
            ->willReturn($lead);

        $entityManager
            ->method('getRepository')
            ->willReturnMap(
                [
                    [Page::class, $pageRepository],
                    [Hit::class, $hitRepository],
                ]
            );

        $coreParametersHelper
            ->method('get')
            ->with($this->anything())
            ->willReturnCallback(function ($parameter) use ($transliterationEnabled, $validatePageHitRequiredData) {
                if ('transliterate_page_title' === $parameter) {
                    return $transliterationEnabled;
                }

                if ('validate_page_hit_required_data' === $parameter) {
                    return $validatePageHitRequiredData;
                }
            });
        $validatorMock               = $this->createMock(ValidatorInterface::class);

        $validatorMock->method('validate')
            ->willReturn(new ConstraintViolationList());

        return new PageModel(
            $this->createStub(CookieHelper::class),
            $this->ipLookupHelper,
            $this->createStub(LeadModel::class),
            $this->createStub(FieldModel::class),
            $redirectModel,
            $this->createStub(TrackableModel::class),
            $this->createStub(MessageBus::class),
            $this->companyModel,
            $this->createStub(DeviceTracker::class),
            $contactTracker,
            $coreParametersHelper,
            $this->contactRequestHelper,
            $this->createStub(VariantConverterService::class),
            $entityManager,
            $this->security = $this->createMock(CorePermissions::class),
            $this->createStub(EventDispatcher::class),
            $this->router,
            $this->createStub(Translator::class),
            $this->createStub(UserHelper::class),
            $this->createStub(LoggerInterface::class),
            $this->createStub(StatRepository::class),
            $this->createStub(BotRatioHelper::class),
            $validatorMock,
            $this->createStub(PageRepository::class), // $pageRepository
            $this->createStub(HitRepository::class), // $hitRepository
            $this->createStub(EmailRepository::class), // $emailRepository
            $this->createStub(UtmTagRepository::class), // $utmTagRepository
            $this->createStub(RedirectRepository::class),
            $this->createStub(TrackableRepository::class),
            $this->createStub(LeadRepository::class)
        );
    }

    /**
     * @return RedirectModel
     */
    protected function getRedirectModel(): MockObject
    {
        $mockRedirectModel = $this->getMockBuilder(RedirectModel::class)
            ->setConstructorArgs([
                $this->createStub(EntityManagerInterface::class),
                $this->createStub(CorePermissions::class),
                $this->createStub(EventDispatcherInterface::class),
                $this->createStub(UrlGeneratorInterface::class),
                $this->createStub(Translator::class),
                $this->createStub(UserHelper::class),
                $this->createStub(LoggerInterface::class),
                $this->createStub(CoreParametersHelper::class),
            ])
            ->onlyMethods(['createRedirectEntity', 'generateRedirectUrl'])
            ->getMock();

        $mockRedirectModel->autowireRedirectModel($this->createMock(Shortener::class), $this->createStub(RedirectRepository::class));

        $mockRedirectModel
            ->method('createRedirectEntity')
            ->willReturn($this->createStub(Redirect::class));

        $mockRedirectModel
            ->method('generateRedirectUrl')
            ->willReturn('http://some-url.com');

        return $mockRedirectModel;
    }
}
