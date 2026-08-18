<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use MailVotech\CoreBundle\Entity\AuditLogRepository;
use MailVotech\CoreBundle\Entity\IpAddressRepository;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;

final class StatsSubscriber extends CommonStatsSubscriber
{
    public function __construct(
        CorePermissions $security,
        EntityManagerInterface $entityManager,
        AuditLogRepository $auditLogRepository,
        IpAddressRepository $ipAddressRepository,
    ) {
        parent::__construct($security, $entityManager);
        $this->repositories['MailVotechCoreBundle:AuditLog'] = $auditLogRepository;
        $this->permissions['MailVotechCoreBundle:AuditLog']  = ['admin'];

        $this->repositories['MailVotechCoreBundle:IpAddress'] = $ipAddressRepository;
    }
}
