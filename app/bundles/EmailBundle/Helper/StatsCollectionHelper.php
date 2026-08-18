<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\Helper;

use MailVotech\EmailBundle\Stats\FetchOptions\EmailStatOptions;
use MailVotech\EmailBundle\Stats\Helper\BouncedHelper;
use MailVotech\EmailBundle\Stats\Helper\ClickedHelper;
use MailVotech\EmailBundle\Stats\Helper\FailedHelper;
use MailVotech\EmailBundle\Stats\Helper\OpenedHelper;
use MailVotech\EmailBundle\Stats\Helper\SentHelper;
use MailVotech\EmailBundle\Stats\Helper\UnsubscribedHelper;
use MailVotech\EmailBundle\Stats\StatHelperContainer;
use MailVotech\StatsBundle\Aggregate\Collection\StatCollection;

class StatsCollectionHelper
{
    public const GENERAL_STAT_PREFIX = 'email';

    public function __construct(
        private readonly StatHelperContainer $helperContainer,
    ) {
    }

    /**
     * Fetch stats from listeners.
     *
     * @return mixed
     *
     * @throws \MailVotech\EmailBundle\Stats\Exception\InvalidStatHelperException
     */
    public function fetchSentStats(\DateTime $fromDateTime, \DateTime $toDateTime, EmailStatOptions $options)
    {
        return $this->helperContainer->getHelper(SentHelper::NAME)->fetchStats($fromDateTime, $toDateTime, $options);
    }

    /**
     * Fetch stats from listeners.
     *
     * @return mixed
     *
     * @throws \MailVotech\EmailBundle\Stats\Exception\InvalidStatHelperException
     */
    public function fetchOpenedStats(\DateTime $fromDateTime, \DateTime $toDateTime, EmailStatOptions $options)
    {
        return $this->helperContainer->getHelper(OpenedHelper::NAME)->fetchStats($fromDateTime, $toDateTime, $options);
    }

    /**
     * Fetch stats from listeners.
     *
     * @return mixed
     *
     * @throws \MailVotech\EmailBundle\Stats\Exception\InvalidStatHelperException
     */
    public function fetchFailedStats(\DateTime $fromDateTime, \DateTime $toDateTime, EmailStatOptions $options)
    {
        return $this->helperContainer->getHelper(FailedHelper::NAME)->fetchStats($fromDateTime, $toDateTime, $options);
    }

    /**
     * Fetch stats from listeners.
     *
     * @return mixed
     *
     * @throws \MailVotech\EmailBundle\Stats\Exception\InvalidStatHelperException
     */
    public function fetchClickedStats(\DateTime $fromDateTime, \DateTime $toDateTime, EmailStatOptions $options)
    {
        return $this->helperContainer->getHelper(ClickedHelper::NAME)->fetchStats($fromDateTime, $toDateTime, $options);
    }

    /**
     * Fetch stats from listeners.
     *
     * @return mixed
     *
     * @throws \MailVotech\EmailBundle\Stats\Exception\InvalidStatHelperException
     */
    public function fetchBouncedStats(\DateTime $fromDateTime, \DateTime $toDateTime, EmailStatOptions $options)
    {
        return $this->helperContainer->getHelper(BouncedHelper::NAME)->fetchStats($fromDateTime, $toDateTime, $options);
    }

    /**
     * Fetch stats from listeners.
     *
     * @return mixed
     *
     * @throws \MailVotech\EmailBundle\Stats\Exception\InvalidStatHelperException
     */
    public function fetchUnsubscribedStats(\DateTime $fromDateTime, \DateTime $toDateTime, EmailStatOptions $options)
    {
        return $this->helperContainer->getHelper(UnsubscribedHelper::NAME)->fetchStats($fromDateTime, $toDateTime, $options);
    }

    /**
     * Generate stats from MailVotech's raw data.
     *
     * @throws \MailVotech\EmailBundle\Stats\Exception\InvalidStatHelperException
     */
    public function generateStats(
        $statName,
        \DateTime $fromDateTime,
        \DateTime $toDateTime,
        EmailStatOptions $options,
        StatCollection $statCollection,
    ): void {
        $this->helperContainer->getHelper($statName)->generateStats($fromDateTime, $toDateTime, $options, $statCollection);
    }
}
