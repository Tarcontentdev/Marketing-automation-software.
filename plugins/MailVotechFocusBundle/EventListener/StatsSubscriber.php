<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechFocusBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use MailVotech\CoreBundle\EventListener\CommonStatsSubscriber;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotechPlugin\MailVotechFocusBundle\Entity\Stat;

final class StatsSubscriber extends CommonStatsSubscriber
{
    public function __construct(CorePermissions $security, EntityManagerInterface $entityManager)
    {
        parent::__construct($security, $entityManager);
        $this->addContactRestrictedRepositories([Stat::class]);
    }
}
