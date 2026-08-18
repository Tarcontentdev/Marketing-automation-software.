<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use MailVotech\CoreBundle\EventListener\CommonStatsSubscriber;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\EmailBundle\Entity\EmailReply;
use MailVotech\EmailBundle\Entity\StatDeviceRepository;
use MailVotech\EmailBundle\Entity\StatRepository;

final class StatsSubscriber extends CommonStatsSubscriber
{
    public function __construct(
        CorePermissions $security,
        EntityManagerInterface $entityManager,
        StatDeviceRepository $statDeviceRepository,
        StatRepository $statRepository,
    ) {
        parent::__construct($security, $entityManager);

        $this->repositories[]                                     = $statDeviceRepository;
        $this->permissions[$statDeviceRepository->getTableName()] = ['stat.lead' => 'lead:leads'];

        $this->addContactRestrictedRepositories([EmailReply::class]);

        $this->repositories[]           = $statRepository;
        $statsTable                     = $statRepository->getTableName();
        $this->permissions[$statsTable] = ['lead' => 'lead:leads'];
        $this->selects[$statsTable]     = [
            'id',
            'email_id',
            'lead_id',
            'list_id',
            'ip_id',
            'email_address',
            'date_sent',
            'is_read',
            'is_failed',
            'viewed_in_browser',
            'date_read',
            'tracking_hash',
            'retry_count',
            'source',
            'source_id',
            'open_count',
            'last_opened',
            'open_details',
        ];
    }
}
