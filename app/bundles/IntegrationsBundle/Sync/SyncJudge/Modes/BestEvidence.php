<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Sync\SyncJudge\Modes;

use MailVotech\IntegrationsBundle\Sync\DAO\Sync\InformationChangeRequestDAO;
use MailVotech\IntegrationsBundle\Sync\Exception\ConflictUnresolvedException;
use MailVotech\IntegrationsBundle\Sync\SyncJudge\SyncJudgeInterface;

final class BestEvidence implements JudgementModeInterface
{
    use DateComparisonTrait;

    /**
     * @throws ConflictUnresolvedException
     */
    public static function adjudicate(
        InformationChangeRequestDAO $leftChangeRequest,
        InformationChangeRequestDAO $rightChangeRequest,
    ): InformationChangeRequestDAO {
        try {
            return HardEvidence::adjudicate($leftChangeRequest, $rightChangeRequest);
        } catch (ConflictUnresolvedException) {
        }

        if (null === $leftChangeRequest->getPossibleChangeDateTime() || null === $rightChangeRequest->getPossibleChangeDateTime()) {
            throw new ConflictUnresolvedException();
        }

        $possibleChangeCompare = self::compareDateTimes(
            $leftChangeRequest->getPossibleChangeDateTime(),
            $rightChangeRequest->getPossibleChangeDateTime()
        );

        if (SyncJudgeInterface::NO_WINNER === $possibleChangeCompare) {
            throw new ConflictUnresolvedException();
        }

        if (SyncJudgeInterface::LEFT_WINNER === $possibleChangeCompare) {
            return $leftChangeRequest;
        }

        return $rightChangeRequest;
    }
}
