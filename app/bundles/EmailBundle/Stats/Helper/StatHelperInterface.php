<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\Stats\Helper;

use MailVotech\EmailBundle\Stats\FetchOptions\EmailStatOptions;
use MailVotech\StatsBundle\Aggregate\Collection\StatCollection;

interface StatHelperInterface
{
    /**
     * @return string
     */
    public function getName();

    public function fetchStats(\DateTime $fromDateTime, \DateTime $toDateTime, EmailStatOptions $options);

    public function generateStats(\DateTime $fromDateTime, \DateTime $toDateTime, EmailStatOptions $options, StatCollection $statCollection);
}
