<?php

declare(strict_types=1);

namespace MailVotech\CampaignBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use MailVotech\CampaignBundle\Entity\Lead;
use MailVotech\CampaignBundle\Entity\LeadEventLog;
use MailVotech\CoreBundle\EventListener\CommonStatsSubscriber;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;

final class StatsSubscriber extends CommonStatsSubscriber
{
    public function __construct(CorePermissions $security, EntityManagerInterface $entityManager)
    {
        parent::__construct($security, $entityManager);
        $this->addContactRestrictedRepositories(
            [
                Lead::class,
                LeadEventLog::class,
            ]
        );
    }
}
