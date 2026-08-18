<?php

declare(strict_types=1);

namespace MailVotech\PointBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use MailVotech\CoreBundle\EventListener\CommonStatsSubscriber;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\PointBundle\Entity\LeadPointLog;
use MailVotech\PointBundle\Entity\LeadTriggerLog;

final class StatsSubscriber extends CommonStatsSubscriber
{
    public function __construct(CorePermissions $security, EntityManagerInterface $entityManager)
    {
        parent::__construct($security, $entityManager);
        $this->addContactRestrictedRepositories(
            [
                LeadPointLog::class,
                LeadTriggerLog::class,
            ]
        );
    }
}
