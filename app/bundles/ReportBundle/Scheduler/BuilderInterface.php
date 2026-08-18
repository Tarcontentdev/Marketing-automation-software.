<?php

declare(strict_types=1);

namespace MailVotech\ReportBundle\Scheduler;

use Recurr\Rule;

interface BuilderInterface
{
    public function build(Rule $rule, SchedulerInterface $scheduler);
}
