<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Sync\SyncJudge\Modes;

use MailVotech\IntegrationsBundle\Sync\DAO\Sync\InformationChangeRequestDAO;
use MailVotech\IntegrationsBundle\Sync\Exception\ConflictUnresolvedException;
use MailVotech\IntegrationsBundle\Sync\SyncJudge\SyncJudgeInterface;

final class HardEvidence implements JudgementModeInterface
{
    use DateComparisonTrait;

    /**
     * @throws ConflictUnresolvedException
     */
    public static function adjudicate(
        InformationChangeRequestDAO $leftChangeRequest,
        InformationChangeRequestDAO $rightChangeRequest,
    ): InformationChangeRequestDAO {
        if (null === $leftChangeRequest->getCertainChangeDateTime() || null === $rightChangeRequest->getCertainChangeDateTime()) {
            throw new ConflictUnresolvedException();
        }

        $certainChangeCompare = self::compareDateTimes(
            $leftChangeRequest->getCertainChangeDateTime(),
            $rightChangeRequest->getCertainChangeDateTime()
        );

        if (SyncJudgeInterface::NO_WINNER === $certainChangeCompare) {
            throw new ConflictUnresolvedException();
        }

        if (SyncJudgeInterface::LEFT_WINNER === $certainChangeCompare) {
            return $leftChangeRequest;
        }

        return $rightChangeRequest;
    }
}
