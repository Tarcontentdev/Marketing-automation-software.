<?php

declare(strict_types=1);

namespace MailVotech\StatsBundle;

final class StatEvents
{
    /**
     * The mailvotech.aggregate_stat_request event is dispatched when an aggregate stat is requested.
     *
     * The event listener receives a \MailVotech\StatsBundle\Event\AggregateStatRequestEvent instance.
     *
     * @var string
     */
    public const AGGREGATE_STAT_REQUEST = 'mailvotech.aggregate_stat_request';
}
