<?php

declare(strict_types=1);

namespace MailVotech\DashboardBundle\Tests\Model;

use Doctrine\ORM\EntityManagerInterface;
use MailVotech\CacheBundle\Cache\CacheProviderTagAwareInterface;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Helper\Filesystem;
use MailVotech\CoreBundle\Helper\PathsHelper;
use MailVotech\CoreBundle\Helper\UserHelper;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\CoreBundle\Translation\Translator;
use MailVotech\DashboardBundle\Entity\Widget;
use MailVotech\DashboardBundle\Entity\WidgetRepository;
use MailVotech\DashboardBundle\Event\WidgetDetailEvent;
use MailVotech\DashboardBundle\Factory\WidgetDetailEventFactory;
use MailVotech\DashboardBundle\Model\DashboardModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class DashboardModelTest extends TestCase
{
    private MockObject&CoreParametersHelper $coreParametersHelper;

    private MockObject&Session $session;

    private DashboardModel $model;

    protected function setUp(): void
    {
        $this->coreParametersHelper = $this->createMock(CoreParametersHelper::class);
        $this->session              = $this->createMock(Session::class);
        $requestStack               = $this->createMock(RequestStack::class);
        $requestStack->method('getSession')
            ->willReturn($this->session);

        $this->model = new DashboardModel(
            $this->coreParametersHelper,
            $this->createStub(PathsHelper::class),
            $this->createStub(WidgetDetailEventFactory::class),
            $this->createStub(Filesystem::class),
            $requestStack,
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(CorePermissions::class),
            $this->createStub(EventDispatcherInterface::class),
            $this->createStub(UrlGeneratorInterface::class),
            $this->createStub(Translator::class),
            $this->createStub(UserHelper::class),
            $this->createStub(LoggerInterface::class),
            $this->createStub(CacheProviderTagAwareInterface::class),
            $this->createStub(WidgetRepository::class), // $widgetRepository
        );
    }

    public function testGetDefaultFilterFromSession(): void
    {
        $dateFromStr = '-1 month';
        $dateFrom    = new \DateTime($dateFromStr);
        $dateTo      = new \DateTime('23:59:59'); // till end of the 'to' date selected

        $this->coreParametersHelper->expects($this->once())
            ->method('get')
            ->with('default_daterange_filter', $dateFromStr)
            ->willReturn($dateFromStr);

        $this->session->expects($this->exactly(2))
            ->method('get')
            ->willReturnOnConsecutiveCalls(
                $dateFrom->format(\DateTimeInterface::ATOM),
                $dateTo->format(\DateTimeInterface::ATOM)
            );

        $filter = $this->model->getDefaultFilter();

        $this->assertSame($dateFrom->format(\DateTimeInterface::ATOM), $filter['dateFrom']->format(\DateTimeInterface::ATOM));

        $this->assertSame($dateTo->format(\DateTimeInterface::ATOM), $filter['dateTo']->format(\DateTimeInterface::ATOM));
    }

    public function testPopulateWidgetContentCatchesExceptionAndSetsGenericErrorMessage(): void
    {
        $widget    = new Widget();
        $exception = new \RuntimeException('DB connection failed — secret host: db.internal');
        $event     = $this->createStub(WidgetDetailEvent::class);

        $widgetEventFactory = $this->createMock(WidgetDetailEventFactory::class);
        $widgetEventFactory->method('create')->willReturn($event);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->willThrowException($exception);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error')
            ->with(
                self::stringContains('failed to load'),
                self::callback(fn (array $ctx): bool => $exception === $ctx['exception'])
            );

        $this->coreParametersHelper->method('get')->willReturn(null);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getSession')->willReturn($this->session);

        $model = new DashboardModel(
            $this->coreParametersHelper,
            $this->createStub(PathsHelper::class),
            $widgetEventFactory,
            $this->createStub(Filesystem::class),
            $requestStack,
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(CorePermissions::class),
            $dispatcher,
            $this->createStub(UrlGeneratorInterface::class),
            $this->createStub(Translator::class),
            $this->createStub(UserHelper::class),
            $logger,
            $this->createStub(CacheProviderTagAwareInterface::class),
            $this->createStub(WidgetRepository::class), // $widgetRepository
        );

        // Pass timezone to skip userHelper->getUser()->getTimezone()
        $model->populateWidgetContent($widget, ['timezone' => 'UTC']);

        $this->assertSame('mailvotech.dashboard.widget.load.failed', $widget->getErrorMessage());
    }
}
