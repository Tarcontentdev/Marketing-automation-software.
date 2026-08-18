<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\Field\Dispatcher;

use MailVotech\LeadBundle\Entity\LeadField;
use MailVotech\LeadBundle\Field\Dispatcher\FieldColumnDispatcher;
use MailVotech\LeadBundle\Field\Event\AddColumnEvent;
use MailVotech\LeadBundle\Field\Event\DeleteColumnEvent;
use MailVotech\LeadBundle\Field\Event\UpdateColumnEvent;
use MailVotech\LeadBundle\Field\Exception\AbortColumnCreateException;
use MailVotech\LeadBundle\Field\Exception\AbortColumnUpdateException;
use MailVotech\LeadBundle\Field\Settings\BackgroundSettings;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class FieldColumnDispatcherTest extends \PHPUnit\Framework\TestCase
{
    public function testNoBackground(): void
    {
        $dispatcher         = $this->createMock(EventDispatcherInterface::class);
        $backgroundSettings = $this->createMock(BackgroundSettings::class);
        $leadField          = new LeadField();

        $backgroundSettings->expects($this->once())
            ->method('shouldProcessColumnChangeInBackground')
            ->willReturn(false);

        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->isInstanceOf(AddColumnEvent::class),
                'mailvotech.lead_field_pre_add_column',
            );

        $fieldColumnDispatcher = new FieldColumnDispatcher($dispatcher, $backgroundSettings);

        $fieldColumnDispatcher->dispatchPreAddColumnEvent($leadField);
    }

    public function testStopPropagation(): void
    {
        $leadField          = new LeadField();
        $dispatcher         = $this->createMock(EventDispatcherInterface::class);
        $backgroundSettings = $this->createMock(BackgroundSettings::class);

        $backgroundSettings->expects($this->once())
            ->method('shouldProcessColumnChangeInBackground')
            ->willReturn(true);

        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->isInstanceOf(AddColumnEvent::class),
                'mailvotech.lead_field_pre_add_column'
            );

        $fieldColumnDispatcher = new FieldColumnDispatcher($dispatcher, $backgroundSettings);

        $this->expectException(AbortColumnCreateException::class);
        $this->expectExceptionMessage('Column change will be processed in background job');

        $fieldColumnDispatcher->dispatchPreAddColumnEvent($leadField);
    }

    public function testStopPropagationUpdate(): void
    {
        $leadField = new LeadField();

        $dispatcher         = $this->createMock(EventDispatcherInterface::class);
        $backgroundSettings = $this->createMock(BackgroundSettings::class);

        $dispatcher
            ->expects($this->once())
            ->method('hasListeners')
            ->willReturn(true);

        $backgroundSettings
            ->expects($this->once())
            ->method('shouldProcessColumnChangeInBackground')
            ->willReturn(true);

        $dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->isInstanceOf(UpdateColumnEvent::class),
                'mailvotech.lead_field_pre_update_column'
            );

        $fieldColumnDispatcher = new FieldColumnDispatcher($dispatcher, $backgroundSettings);

        $this->expectException(AbortColumnUpdateException::class);
        $this->expectExceptionMessage('Column change will be processed in background job');

        $fieldColumnDispatcher->dispatchPreUpdateColumnEvent($leadField);
    }

    public function testStopPropagationDelete(): void
    {
        $leadField          = new LeadField();
        $dispatcher         = $this->createMock(EventDispatcherInterface::class);
        $backgroundSettings = $this->createMock(BackgroundSettings::class);

        $dispatcher->expects($this->once())
            ->method('hasListeners')
            ->willReturn(true);

        $backgroundSettings->expects($this->once())
            ->method('shouldProcessColumnChangeInBackground')
            ->willReturn(true);

        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->isInstanceOf(DeleteColumnEvent::class),
                'mailvotech.lead_field_pre_delete_column',
            );

        $fieldColumnDispatcher = new FieldColumnDispatcher($dispatcher, $backgroundSettings);

        $this->expectException(AbortColumnUpdateException::class);
        $this->expectExceptionMessage('Column delete will be processed in background job');

        $fieldColumnDispatcher->dispatchPreDeleteColumnEvent($leadField);
    }
}
