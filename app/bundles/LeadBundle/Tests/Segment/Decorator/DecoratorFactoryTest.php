<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\Segment\Decorator;

use MailVotech\LeadBundle\Event\LeadListFiltersDecoratorDelegateEvent;
use MailVotech\LeadBundle\LeadEvents;
use MailVotech\LeadBundle\Segment\ContactSegmentFilterCrate;
use MailVotech\LeadBundle\Segment\Decorator\BaseDecorator;
use MailVotech\LeadBundle\Segment\Decorator\CompanyDecorator;
use MailVotech\LeadBundle\Segment\Decorator\CustomMappedDecorator;
use MailVotech\LeadBundle\Segment\Decorator\Date\DateOptionFactory;
use MailVotech\LeadBundle\Segment\Decorator\DecoratorFactory;
use MailVotech\LeadBundle\Segment\Decorator\FilterDecoratorInterface;
use MailVotech\LeadBundle\Services\ContactSegmentFilterDictionary;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class DecoratorFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MockObject&EventDispatcherInterface
     */
    private MockObject $eventDispatcherMock;

    /**
     * @var MockObject&DateOptionFactory
     */
    private MockObject $dateOptionFactory;

    private DecoratorFactory $decoratorFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventDispatcherMock            = $this->createMock(EventDispatcherInterface::class);
        $contactSegmentFilterDictionary       = new ContactSegmentFilterDictionary($this->eventDispatcherMock);
        $this->dateOptionFactory              = $this->createMock(DateOptionFactory::class);
        $this->decoratorFactory               = new DecoratorFactory(
            $contactSegmentFilterDictionary,
            $this->createStub(BaseDecorator::class),
            $this->createStub(CustomMappedDecorator::class),
            $this->dateOptionFactory,
            $this->createStub(CompanyDecorator::class),
            $this->eventDispatcherMock);
    }

    public function testBaseDecorator(): void
    {
        $contactSegmentFilterCrate = new ContactSegmentFilterCrate([
            'field'    => 'date_identified',
            'type'     => 'number',
        ]);

        $this->assertInstanceOf(
            BaseDecorator::class,
            $this->decoratorFactory->getDecoratorForFilter($contactSegmentFilterCrate)
        );
    }

    public function testCustomMappedDecorator(): void
    {
        $contactSegmentFilterCrate = new ContactSegmentFilterCrate([
            'field'    => 'hit_url_count',
            'type'     => 'number',
        ]);

        $this->assertInstanceOf(
            CustomMappedDecorator::class,
            $this->decoratorFactory->getDecoratorForFilter($contactSegmentFilterCrate)
        );
    }

    public function testDateDecoratorWhenNoSubscriberProvidesDecorator(): void
    {
        $filterDecoratorInterface  = $this->createStub(FilterDecoratorInterface::class);
        $contactSegmentFilterCrate = new ContactSegmentFilterCrate(['type' => 'date']);

        $this->dateOptionFactory->expects($this->once())
            ->method('getDateOption')
            ->with($contactSegmentFilterCrate)
            ->willReturn($filterDecoratorInterface);

        $this->eventDispatcherMock->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->callback(
                    function (LeadListFiltersDecoratorDelegateEvent $event) use ($contactSegmentFilterCrate): true {
                        $this->assertNotInstanceOf(FilterDecoratorInterface::class, $event->getDecorator());
                        $this->assertSame($contactSegmentFilterCrate, $event->getCrate());

                        return true;
                    }
                ),
                LeadEvents::SEGMENT_ON_DECORATOR_DELEGATE
            );

        $this->assertSame(
            $filterDecoratorInterface,
            $this->decoratorFactory->getDecoratorForFilter($contactSegmentFilterCrate)
        );
    }

    public function testDateDecoratorWhenSubscriberProvidesDecorator(): void
    {
        $filterDecoratorInterface  = $this->createStub(FilterDecoratorInterface::class);
        $contactSegmentFilterCrate = new ContactSegmentFilterCrate(['type' => 'date']);

        $this->dateOptionFactory->expects($this->never())
            ->method('getDateOption');

        $this->eventDispatcherMock->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->callback(
                    function (LeadListFiltersDecoratorDelegateEvent $event) use ($contactSegmentFilterCrate, $filterDecoratorInterface): true {
                        $this->assertNotInstanceOf(FilterDecoratorInterface::class, $event->getDecorator());
                        $this->assertSame($contactSegmentFilterCrate, $event->getCrate());

                        $event->setDecorator($filterDecoratorInterface);

                        return true;
                    }
                ),
                LeadEvents::SEGMENT_ON_DECORATOR_DELEGATE
            );

        $this->assertSame(
            $filterDecoratorInterface,
            $this->decoratorFactory->getDecoratorForFilter($contactSegmentFilterCrate)
        );
    }
}
