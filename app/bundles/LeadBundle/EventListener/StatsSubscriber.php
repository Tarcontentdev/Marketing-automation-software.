<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use MailVotech\CoreBundle\EventListener\CommonStatsSubscriber;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\LeadBundle\Entity\CompanyChangeLog;
use MailVotech\LeadBundle\Entity\CompanyLead;
use MailVotech\LeadBundle\Entity\DoNotContact;
use MailVotech\LeadBundle\Entity\FrequencyRule;
use MailVotech\LeadBundle\Entity\LeadCategory;
use MailVotech\LeadBundle\Entity\LeadDevice;
use MailVotech\LeadBundle\Entity\LeadEventLog;
use MailVotech\LeadBundle\Entity\ListLead;
use MailVotech\LeadBundle\Entity\PointsChangeLog;
use MailVotech\LeadBundle\Entity\StagesChangeLog;
use MailVotech\LeadBundle\Entity\UtmTag;

final class StatsSubscriber extends CommonStatsSubscriber
{
    public function __construct(CorePermissions $security, EntityManagerInterface $entityManager)
    {
        parent::__construct($security, $entityManager);
        $this->addContactRestrictedRepositories(
            [
                CompanyChangeLog::class,
                PointsChangeLog::class,
                StagesChangeLog::class,
                CompanyLead::class,
                LeadCategory::class,
                LeadDevice::class,
                LeadEventLog::class,
                ListLead::class,
                DoNotContact::class,
                FrequencyRule::class,
                UtmTag::class,
            ]
        );
    }
}
