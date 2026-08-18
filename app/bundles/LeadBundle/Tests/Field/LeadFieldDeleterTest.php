<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\Field;

use MailVotech\CoreBundle\Helper\UserHelper;
use MailVotech\LeadBundle\Entity\LeadField;
use MailVotech\LeadBundle\Entity\LeadFieldRepository;
use MailVotech\LeadBundle\Exception\NoListenerException;
use MailVotech\LeadBundle\Field\Dispatcher\FieldDeleteDispatcher;
use MailVotech\LeadBundle\Field\LeadFieldDeleter;
use MailVotech\LeadBundle\Field\Settings\BackgroundSettings;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class LeadFieldDeleterTest extends TestCase
{
    private MockObject&LeadFieldRepository $leadFieldRepositoryMock;

    private MockObject&FieldDeleteDispatcher $fieldDeleteDispatcherMock;

    private MockObject&BackgroundSettings $backgroundSettingsMock;

    private LeadFieldDeleter $leadFieldDeleter;

    protected function setUp(): void
    {
        $this->leadFieldRepositoryMock   = $this->createMock(LeadFieldRepository::class);
        $this->fieldDeleteDispatcherMock = $this->createMock(FieldDeleteDispatcher::class);
        $this->backgroundSettingsMock    = $this->createMock(BackgroundSettings::class);
        $this->leadFieldDeleter          = new LeadFieldDeleter(
            $this->leadFieldRepositoryMock,
            $this->fieldDeleteDispatcherMock,
            $this->createStub(UserHelper::class),
            $this->backgroundSettingsMock,
        );
    }

    public function testDeleteLeadFieldEntityNoBackground(): void
    {
        $leadField = new LeadField();
        $this->backgroundSettingsMock
            ->expects($this->once())
            ->method('shouldProcessColumnChangeInBackground')
            ->willReturn(true);
        $this->leadFieldRepositoryMock
            ->expects($this->never())
            ->method('deleteEntity');
        $this->leadFieldDeleter->deleteLeadFieldEntity($leadField);
    }

    public function testDeleteLeadFieldEntityInBackground(): void
    {
        $leadField = new LeadField();
        $this->backgroundSettingsMock
            ->expects($this->once())
            ->method('shouldProcessColumnChangeInBackground')
            ->willReturn(true);
        $this->leadFieldRepositoryMock
            ->expects($this->once())
            ->method('deleteEntity')
            ->with($leadField);
        $this->fieldDeleteDispatcherMock
            ->expects($this->once())
            ->method('dispatchPostDeleteEvent')
            ->with($leadField)
            ->willThrowException(new NoListenerException());
        $this->leadFieldDeleter->deleteLeadFieldEntity($leadField, true);
    }
}
