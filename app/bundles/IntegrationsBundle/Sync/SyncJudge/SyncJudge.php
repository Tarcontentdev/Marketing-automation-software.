<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Sync\SyncJudge;

use MailVotech\IntegrationsBundle\Sync\DAO\Sync\InformationChangeRequestDAO;
use MailVotech\IntegrationsBundle\Sync\Exception\ConflictUnresolvedException;
use MailVotech\IntegrationsBundle\Sync\SyncJudge\Modes\BestEvidence;
use MailVotech\IntegrationsBundle\Sync\SyncJudge\Modes\FuzzyEvidence;
use MailVotech\IntegrationsBundle\Sync\SyncJudge\Modes\HardEvidence;

final class SyncJudge implements SyncJudgeInterface
{
    /**
     * @param string $mode
     *
     * @return InformationChangeRequestDAO
     *
     * @throws ConflictUnresolvedException
     */
    public function adjudicate(
        $mode,
        InformationChangeRequestDAO $leftChangeRequest,
        InformationChangeRequestDAO $rightChangeRequest,
    ) {
        if ($leftChangeRequest->getNewValue() === $rightChangeRequest->getNewValue()) {
            return $leftChangeRequest;
        }

        return match ($mode) {
            SyncJudgeInterface::HARD_EVIDENCE_MODE => HardEvidence::adjudicate($leftChangeRequest, $rightChangeRequest),
            SyncJudgeInterface::BEST_EVIDENCE_MODE => BestEvidence::adjudicate($leftChangeRequest, $rightChangeRequest),
            default                                => FuzzyEvidence::adjudicate($leftChangeRequest, $rightChangeRequest),
        };
    }
}
