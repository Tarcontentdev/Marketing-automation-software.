<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Tests\Unit\Sync\SyncJudge\Modes;

use MailVotech\IntegrationsBundle\Sync\DAO\Sync\InformationChangeRequestDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Value\NormalizedValueDAO;
use MailVotech\IntegrationsBundle\Sync\Exception\ConflictUnresolvedException;
use MailVotech\IntegrationsBundle\Sync\SyncJudge\Modes\HardEvidence;
use PHPUnit\Framework\TestCase;

final class HardEvidenceTest extends TestCase
{
    public function testLeftWinner(): void
    {
        $leftChangeRequest = new InformationChangeRequestDAO(
            'Test',
            'Object',
            1,
            'field',
            new NormalizedValueDAO(NormalizedValueDAO::TEXT_TYPE, 'test')
        );
        $leftChangeRequest->setCertainChangeDateTime(new \DateTimeImmutable('2018-10-08 00:01:00'));

        $rightChangeRequest = new InformationChangeRequestDAO(
            'Test',
            'Object',
            1,
            'field',
            new NormalizedValueDAO(NormalizedValueDAO::TEXT_TYPE, 'test')
        );
        $rightChangeRequest->setCertainChangeDateTime(new \DateTimeImmutable('2018-10-08 00:00:00'));

        $winner = HardEvidence::adjudicate($leftChangeRequest, $rightChangeRequest);

        $this->assertEquals($leftChangeRequest, $winner);
    }

    public function testRightWinner(): void
    {
        $leftChangeRequest = new InformationChangeRequestDAO(
            'Test',
            'Object',
            1,
            'field',
            new NormalizedValueDAO(NormalizedValueDAO::TEXT_TYPE, 'test')
        );
        $leftChangeRequest->setCertainChangeDateTime(new \DateTimeImmutable('2018-10-08 00:00:00'));

        $rightChangeRequest = new InformationChangeRequestDAO(
            'Test',
            'Object',
            1,
            'field',
            new NormalizedValueDAO(NormalizedValueDAO::TEXT_TYPE, 'test')
        );
        $rightChangeRequest->setCertainChangeDateTime(new \DateTimeImmutable('2018-10-08 00:01:00'));

        $winner = HardEvidence::adjudicate($leftChangeRequest, $rightChangeRequest);

        $this->assertEquals($rightChangeRequest, $winner);
    }

    public function testUnresolvedConflictExceptionThrownIfEqual(): void
    {
        $leftChangeRequest = new InformationChangeRequestDAO(
            'Test',
            'Object',
            1,
            'field',
            new NormalizedValueDAO(NormalizedValueDAO::TEXT_TYPE, 'test')
        );
        $leftChangeRequest->setCertainChangeDateTime(new \DateTimeImmutable('2018-10-08 00:00:00'));

        $rightChangeRequest = new InformationChangeRequestDAO(
            'Test',
            'Object',
            1,
            'field',
            new NormalizedValueDAO(NormalizedValueDAO::TEXT_TYPE, 'test')
        );
        $rightChangeRequest->setCertainChangeDateTime(new \DateTimeImmutable('2018-10-08 00:00:00'));

        $this->expectException(ConflictUnresolvedException::class);
        HardEvidence::adjudicate($leftChangeRequest, $rightChangeRequest);
    }

    public function testUnresolvedConflictExceptionThrownWhenLeftCertainChangeDateTimeIsNull(): void
    {
        $leftChangeRequest = new InformationChangeRequestDAO(
            'Test',
            'Object',
            1,
            'field',
            new NormalizedValueDAO(NormalizedValueDAO::TEXT_TYPE, 'test')
        );

        $rightChangeRequest = new InformationChangeRequestDAO(
            'Test',
            'Object',
            1,
            'field',
            new NormalizedValueDAO(NormalizedValueDAO::TEXT_TYPE, 'test')
        );
        $rightChangeRequest->setCertainChangeDateTime(new \DateTimeImmutable('2018-10-08 00:00:00'));

        $this->expectException(ConflictUnresolvedException::class);
        HardEvidence::adjudicate($leftChangeRequest, $rightChangeRequest);
    }

    public function testUnresolvedConflictExceptionThrownWhenRightCertainChangeDateTimeIsNull(): void
    {
        $leftChangeRequest = new InformationChangeRequestDAO(
            'Test',
            'Object',
            1,
            'field',
            new NormalizedValueDAO(NormalizedValueDAO::TEXT_TYPE, 'test')
        );
        $leftChangeRequest->setCertainChangeDateTime(new \DateTimeImmutable('2018-10-08 00:00:00'));

        $rightChangeRequest = new InformationChangeRequestDAO(
            'Test',
            'Object',
            1,
            'field',
            new NormalizedValueDAO(NormalizedValueDAO::TEXT_TYPE, 'test')
        );

        $this->expectException(ConflictUnresolvedException::class);
        HardEvidence::adjudicate($leftChangeRequest, $rightChangeRequest);
    }
}
