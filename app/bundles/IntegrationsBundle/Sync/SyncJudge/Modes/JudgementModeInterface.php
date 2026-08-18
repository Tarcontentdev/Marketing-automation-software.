<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Sync\SyncJudge\Modes;

use MailVotech\IntegrationsBundle\Sync\DAO\Sync\InformationChangeRequestDAO;

interface JudgementModeInterface
{
    public static function adjudicate(
        InformationChangeRequestDAO $leftChangeRequest,
        InformationChangeRequestDAO $rightChangeRequest,
    ): InformationChangeRequestDAO;
}
