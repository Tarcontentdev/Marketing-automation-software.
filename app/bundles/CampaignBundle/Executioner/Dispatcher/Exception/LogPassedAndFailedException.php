<?php

declare(strict_types=1);

namespace MailVotech\CampaignBundle\Executioner\Dispatcher\Exception;

use MailVotech\CampaignBundle\Entity\LeadEventLog;

final class LogPassedAndFailedException extends \Exception
{
    public function __construct(LeadEventLog $log)
    {
        parent::__construct("LeadEventLog ID # {$log->getId()} was passed to both pass() or fail(). Pass or fail the log, not both.");
    }
}
