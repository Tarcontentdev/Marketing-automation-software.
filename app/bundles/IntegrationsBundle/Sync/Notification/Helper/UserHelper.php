<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Sync\Notification\Helper;

use Doctrine\DBAL\Connection;

class UserHelper
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function getAdminUsers(): array
    {
        $qb      = $this->connection->createQueryBuilder();
        $results = $qb->select('u.id')
            ->from(MAILVOTECH_TABLE_PREFIX.'users', 'u')
            ->join('u', MAILVOTECH_TABLE_PREFIX.'roles', 'r', 'r.id = u.role_id')
            ->where(
                $qb->expr()->and(
                    $qb->expr()->eq('r.is_published', 1),
                    $qb->expr()->eq('r.is_admin', 1),
                    $qb->expr()->eq('u.is_published', 1)
                )
            )
            ->executeQuery()
            ->fetchAllAssociative();

        $admins = [];
        foreach ($results as $result) {
            $admins[] = (int) $result['id'];
        }

        return $admins;
    }
}
