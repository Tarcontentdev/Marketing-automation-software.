<?php

declare(strict_types=1);

namespace MailVotech\ReportBundle\Scheduler\Builder;

use MailVotech\ReportBundle\Scheduler\BuilderInterface;
use MailVotech\ReportBundle\Scheduler\Exception\InvalidSchedulerException;
use MailVotech\ReportBundle\Scheduler\SchedulerInterface;
use Recurr\Exception\InvalidArgument;
use Recurr\Rule;

final class SchedulerNowBuilder implements BuilderInterface
{
    /**
     * @throws InvalidSchedulerException
     */
    public function build(Rule $rule, SchedulerInterface $scheduler): Rule
    {
        try {
            $rule->setFreq('SECONDLY');
        } catch (InvalidArgument) {
            throw new InvalidSchedulerException();
        }

        return $rule;
    }
}
