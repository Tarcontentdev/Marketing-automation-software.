<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Tests\Unit\Sync\SyncProcess\Direction\Helper;

use MailVotech\IntegrationsBundle\Exception\RequiredValueException;
use MailVotech\IntegrationsBundle\Sync\DAO\Mapping\ObjectMappingDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Report\FieldDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Value\NormalizedValueDAO;
use MailVotech\IntegrationsBundle\Sync\SyncProcess\Direction\Helper\ValueHelper;
use PHPUnit\Framework\TestCase;

final class ValueHelperTest extends TestCase
{
    public function testExceptionForMissingRequiredIntegrationValue(): void
    {
        $this->expectException(RequiredValueException::class);

        $normalizedValueDAO = new NormalizedValueDAO(NormalizedValueDAO::STRING_TYPE, '');

        $this->getValueHelper()->getValueForIntegration(
            $normalizedValueDAO,
            FieldDAO::FIELD_REQUIRED,
            ObjectMappingDAO::SYNC_TO_INTEGRATION
        );
    }

    public function testNoExceptionForMissingNonRequiredIntegrationValue(): void
    {
        $normalizedValueDAO = new NormalizedValueDAO(NormalizedValueDAO::STRING_TYPE, '');

        $newValue = $this->getValueHelper()->getValueForIntegration(
            $normalizedValueDAO,
            FieldDAO::FIELD_CHANGED,
            ObjectMappingDAO::SYNC_TO_MAILVOTECH
        );

        $this->assertEquals(
            '',
            $newValue->getNormalizedValue()
        );
    }

    public function testNoExceptionForMissingOppositeSyncIntegrationValue(): void
    {
        $normalizedValueDAO = new NormalizedValueDAO(NormalizedValueDAO::STRING_TYPE, '');

        $newValue = $this->getValueHelper()->getValueForIntegration(
            $normalizedValueDAO,
            FieldDAO::FIELD_CHANGED,
            ObjectMappingDAO::SYNC_TO_INTEGRATION
        );

        $this->assertEquals(
            '',
            $newValue->getNormalizedValue()
        );
    }

    public function testExceptionForMissingRequiredMailVotechValue(): void
    {
        $this->expectException(RequiredValueException::class);

        $normalizedValueDAO = new NormalizedValueDAO(NormalizedValueDAO::STRING_TYPE, '');

        $this->getValueHelper()->getValueForMailVotech(
            $normalizedValueDAO,
            FieldDAO::FIELD_REQUIRED,
            ObjectMappingDAO::SYNC_TO_MAILVOTECH
        );
    }

    public function testNoExceptionForMissingNonRequiredInternalValue(): void
    {
        $normalizedValueDAO = new NormalizedValueDAO(NormalizedValueDAO::STRING_TYPE, '');

        $newValue = $this->getValueHelper()->getValueForMailVotech(
            $normalizedValueDAO,
            FieldDAO::FIELD_CHANGED,
            ObjectMappingDAO::SYNC_TO_INTEGRATION
        );

        $this->assertEquals(
            '',
            $newValue->getNormalizedValue()
        );
    }

    public function testNoExceptionForMissingOppositeSyncInternalnValue(): void
    {
        $normalizedValueDAO = new NormalizedValueDAO(NormalizedValueDAO::STRING_TYPE, '');

        $newValue = $this->getValueHelper()->getValueForMailVotech(
            $normalizedValueDAO,
            FieldDAO::FIELD_CHANGED,
            ObjectMappingDAO::SYNC_TO_MAILVOTECH
        );

        $this->assertEquals(
            '',
            $newValue->getNormalizedValue()
        );
    }

    private function getValueHelper(): ValueHelper
    {
        return new ValueHelper();
    }
}
