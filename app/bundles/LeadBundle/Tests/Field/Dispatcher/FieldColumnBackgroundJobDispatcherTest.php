<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\Field\Dispatcher;

use MailVotech\LeadBundle\Entity\LeadField;
use MailVotech\LeadBundle\Exception\NoListenerException;
use MailVotech\LeadBundle\Field\Dispatcher\FieldColumnBackgroundJobDispatcher;
use MailVotech\LeadBundle\Field\Event\AddColumnBackgroundEvent;
use MailVotech\LeadBundle\Field\Event\DeleteColumnBackgroundEvent;
use MailVotech\LeadBundle\Field\Event\UpdateColumnBackgroundEvent;
use MailVotech\LeadBundle\Field\Exception\AbortColumnCreateException;
use MailVotech\LeadBundle\Field\Exception\AbortColumnUpdateException;
use MailVotech\LeadBundle\LeadEvents;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class FieldColumnBackgroundJobDispatcherTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MockObject&EventDispatcherInterface
     */
    private MockObject $dispatcher;

    protected function setUp(): void
    {
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);

        parent::setUp();
    }

    public function testNoListener(): void
    {
        $this->dispatcher->expects($this->once())->method('hasListeners')->willReturn(false);
        $this->dispatcher->expects($this->never())->method('dispatch');

        $fieldColumnBackgroundJobDispatcher = new FieldColumnBackgroundJobDispatcher($this->dispatcher);

        $this->expectException(NoListenerException::class);
        $this->expectExceptionMessage('There is no Listener for this event');

        $fieldColumnBackgroundJobDispatcher->dispatchPreAddColumnEvent(new LeadField());
    }

    public function testNoListenerUpdate(): void
    {
        $this->dispatcher->expects($this->once())->method('hasListeners')->willReturn(false);
        $this->dispatcher->expects($this->never())->method('dispatch');

        $fieldColumnBackgroundJobDispatcher = new FieldColumnBackgroundJobDispatcher($this->dispatcher);

        $this->expectException(NoListenerException::class);
        $this->expectExceptionMessage('There is no Listener for this event');

        $fieldColumnBackgroundJobDispatcher->dispatchPreUpdateColumnEvent(new LeadField());
    }

    public function testNoListenerDelete(): void
    {
        $this->dispatcher->expects($this->once())->method('hasListeners')->willReturn(false);
        $this->dispatcher->expects($this->never())->method('dispatch');

        $fieldColumnBackgroundJobDispatcher = new FieldColumnBackgroundJobDispatcher($this->dispatcher);

        $this->expectException(NoListenerException::class);
        $this->expectExceptionMessage('There is no Listener for this event');

        $fieldColumnBackgroundJobDispatcher->dispatchPreDeleteColumnEvent(new LeadField());
    }

    public function testNormalProcess(): void
    {
        $this->dispatcher->expects($this->once())->method('hasListeners')->willReturn(true);
        $this->dispatcher->expects($this->once())->method('dispatch')->with(
            $this->isInstanceOf(AddColumnBackgroundEvent::class),
            LeadEvents::LEAD_FIELD_PRE_ADD_COLUMN_BACKGROUND_JOB,
        );

        $fieldColumnBackgroundJobDispatcher = new FieldColumnBackgroundJobDispatcher($this->dispatcher);
        $fieldColumnBackgroundJobDispatcher->dispatchPreAddColumnEvent(new LeadField());
    }

    public function testNormalProcessUpdate(): void
    {
        $this->dispatcher->expects($this->once())->method('hasListeners')->willReturn(true);
        $this->dispatcher->expects($this->once())->method('dispatch')->with(
            $this->isInstanceOf(UpdateColumnBackgroundEvent::class),
            LeadEvents::LEAD_FIELD_PRE_UPDATE_COLUMN_BACKGROUND_JOB,
        );

        $fieldColumnBackgroundJobDispatcher = new FieldColumnBackgroundJobDispatcher($this->dispatcher);
        $fieldColumnBackgroundJobDispatcher->dispatchPreUpdateColumnEvent(new LeadField());
    }

    public function testNormalProcessDelete(): void
    {
        $this->dispatcher->expects($this->once())->method('hasListeners')->willReturn(true);
        $this->dispatcher->expects($this->once())->method('dispatch')->with(
            $this->isInstanceOf(DeleteColumnBackgroundEvent::class),
            LeadEvents::LEAD_FIELD_PRE_DELETE_COLUMN_BACKGROUND_JOB,
        );

        $fieldColumnBackgroundJobDispatcher = new FieldColumnBackgroundJobDispatcher($this->dispatcher);
        $fieldColumnBackgroundJobDispatcher->dispatchPreDeleteColumnEvent(new LeadField());
    }

    public function testStopPropagation(): void
    {
        $this->dispatcher->expects($this->once())->method('hasListeners')->willReturn(true);
        $this->dispatcher->expects($this->once())->method('dispatch')->with(
            $this->callback(function (AddColumnBackgroundEvent $event): true {
                $event->stopPropagation();

                return true;
            }),
            LeadEvents::LEAD_FIELD_PRE_ADD_COLUMN_BACKGROUND_JOB,
        );

        $fieldColumnBackgroundJobDispatcher = new FieldColumnBackgroundJobDispatcher($this->dispatcher);

        $this->expectException(AbortColumnCreateException::class);
        $this->expectExceptionMessage('Column cannot be created now');

        $fieldColumnBackgroundJobDispatcher->dispatchPreAddColumnEvent(new LeadField());
    }

    public function testStopPropagationUpdate(): void
    {
        $this->dispatcher->expects($this->once())->method('hasListeners')->willReturn(true);
        $this->dispatcher->expects($this->once())->method('dispatch')->with(
            $this->callback(function (UpdateColumnBackgroundEvent $event): true {
                $event->stopPropagation();

                return true;
            }),
            LeadEvents::LEAD_FIELD_PRE_UPDATE_COLUMN_BACKGROUND_JOB,
        );

        $fieldColumnBackgroundJobDispatcher = new FieldColumnBackgroundJobDispatcher($this->dispatcher);

        $this->expectException(AbortColumnUpdateException::class);
        $this->expectExceptionMessage('Column cannot be updated now');

        $fieldColumnBackgroundJobDispatcher->dispatchPreUpdateColumnEvent(new LeadField());
    }

    public function testStopPropagationDelete(): void
    {
        $this->dispatcher->expects($this->once())->method('hasListeners')->willReturn(true);
        $this->dispatcher->expects($this->once())->method('dispatch')->with(
            $this->callback(function (DeleteColumnBackgroundEvent $event): true {
                $event->stopPropagation();

                return true;
            }),
            LeadEvents::LEAD_FIELD_PRE_DELETE_COLUMN_BACKGROUND_JOB,
        );

        $fieldColumnBackgroundJobDispatcher = new FieldColumnBackgroundJobDispatcher($this->dispatcher);

        $this->expectException(AbortColumnUpdateException::class);
        $this->expectExceptionMessage('Column cannot be deleted now');

        $fieldColumnBackgroundJobDispatcher->dispatchPreDeleteColumnEvent(new LeadField());
    }
}
