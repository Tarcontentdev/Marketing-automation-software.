<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\Field;

use MailVotech\LeadBundle\Entity\LeadField;
use MailVotech\LeadBundle\Entity\LeadFieldRepository;
use MailVotech\LeadBundle\Event\LeadFieldEvent;
use MailVotech\LeadBundle\Field\Dispatcher\FieldSaveDispatcher;
use MailVotech\LeadBundle\Field\LeadFieldSaver;

final class LeadFieldSaverTest extends \PHPUnit\Framework\TestCase
{
    public function testSave(): void
    {
        $leadFieldRepository = $this->createStub(LeadFieldRepository::class);
        $fieldSaveDispatcher = $this->createMock(FieldSaveDispatcher::class);

        $leadFieldSaver = new LeadFieldSaver($leadFieldRepository, $fieldSaveDispatcher);

        $leadField = new LeadField();

        $fieldSaveDispatcher->expects($this->once())
            ->method('dispatchPreSaveEvent')
            ->with($leadField, true)
            ->willReturn(new LeadFieldEvent($leadField));

        $fieldSaveDispatcher->expects($this->once())
            ->method('dispatchPostSaveEvent')
            ->with($leadField, true)
            ->willReturn(new LeadFieldEvent($leadField));

        $leadFieldSaver->saveLeadFieldEntity($leadField, true);
    }

    public function testSaveNoColumnCreated(): void
    {
        $leadFieldRepository = $this->createStub(LeadFieldRepository::class);
        $fieldSaveDispatcher = $this->createMock(FieldSaveDispatcher::class);

        $leadFieldSaver = new LeadFieldSaver($leadFieldRepository, $fieldSaveDispatcher);

        $leadField = new LeadField();

        $fieldSaveDispatcher->expects($this->once())
            ->method('dispatchPreSaveEvent')
            ->with($leadField, true)
            ->willReturn(new LeadFieldEvent($leadField));

        $fieldSaveDispatcher->expects($this->once())
            ->method('dispatchPostSaveEvent')
            ->with($leadField, true)
            ->willReturn(new LeadFieldEvent($leadField));

        $leadFieldSaver->saveLeadFieldEntityWithoutColumnCreated($leadField);

        $this->assertTrue($leadField->getColumnIsNotCreated());
    }
}
