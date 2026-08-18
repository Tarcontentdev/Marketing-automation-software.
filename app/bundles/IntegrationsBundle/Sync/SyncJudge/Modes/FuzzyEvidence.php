<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Sync\SyncJudge\Modes;

use MailVotech\IntegrationsBundle\Sync\DAO\Sync\InformationChangeRequestDAO;
use MailVotech\IntegrationsBundle\Sync\Exception\ConflictUnresolvedException;

final class FuzzyEvidence implements JudgementModeInterface
{
    /**
     * @throws ConflictUnresolvedException
     */
    public static function adjudicate(
        InformationChangeRequestDAO $leftChangeRequest,
        InformationChangeRequestDAO $rightChangeRequest,
    ): InformationChangeRequestDAO {
        try {
            return BestEvidence::adjudicate($leftChangeRequest, $rightChangeRequest);
        } catch (ConflictUnresolvedException) {
        }

        if (
            $leftChangeRequest->getCertainChangeDateTime()
            && $rightChangeRequest->getPossibleChangeDateTime()
            && $leftChangeRequest->getCertainChangeDateTime() > $rightChangeRequest->getPossibleChangeDateTime()
        ) {
            return $leftChangeRequest;
        }

        if (
            $rightChangeRequest->getCertainChangeDateTime()
            && $leftChangeRequest->getPossibleChangeDateTime()
            && $rightChangeRequest->getCertainChangeDateTime() > $leftChangeRequest->getPossibleChangeDateTime()
        ) {
            return $rightChangeRequest;
        }

        throw new ConflictUnresolvedException();
    }
}
