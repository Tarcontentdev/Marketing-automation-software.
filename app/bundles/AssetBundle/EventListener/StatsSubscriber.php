<?php

declare(strict_types=1);

namespace MailVotech\AssetBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use MailVotech\AssetBundle\Entity\Download;
use MailVotech\CoreBundle\EventListener\CommonStatsSubscriber;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;

final class StatsSubscriber extends CommonStatsSubscriber
{
    public function __construct(CorePermissions $security, EntityManagerInterface $entityManager)
    {
        parent::__construct($security, $entityManager);
        $this->addContactRestrictedRepositories([Download::class]);
    }
}
