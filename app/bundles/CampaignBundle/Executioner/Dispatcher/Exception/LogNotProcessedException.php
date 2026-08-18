<?php

declare(strict_types=1);

namespace MailVotech\CampaignBundle\Executioner\Dispatcher\Exception;

use MailVotech\CampaignBundle\Entity\LeadEventLog;

final class LogNotProcessedException extends \Exception
{
    public function __construct(LeadEventLog $log)
    {
        parent::__construct("LeadEventLog ID # {$log->getId()} must be passed to either pass() or fail()");
    }
}
