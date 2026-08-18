<?php

declare(strict_types=1);

namespace MailVotech\WebhookBundle\Entity;

use Doctrine\DBAL\ArrayParameterType;
use MailVotech\CoreBundle\Entity\CommonRepository;
use MailVotech\CoreBundle\Helper\DateTimeHelper;

/**
 * @extends CommonRepository<WebhookQueue>
 */
class WebhookQueueRepository extends CommonRepository
{
    /**
     * Deletes all the webhook queues by ID.
     *
     * @param array $idList of webhookqueue IDs
     */
    public function deleteQueuesById(array $idList): void
    {
        // don't process the list if there are no items in it
        if (!count($idList)) {
            return;
        }

        $qb = $this->_em->getConnection()->createQueryBuilder();
        $qb->delete(MAILVOTECH_TABLE_PREFIX.'webhook_queue')
            ->where(
                $qb->expr()->in('id', ':ids')
            )
            ->setParameter('ids', $idList, ArrayParameterType::INTEGER)
            ->executeStatement();
    }

    /**
     * @param array<int> $idList
     */
    public function incrementRetryCount(array $idList): void
    {
        if (!count($idList)) {
            return;
        }

        $qb = $this->_em->getConnection()->createQueryBuilder();
        $qb->update(MAILVOTECH_TABLE_PREFIX.'webhook_queue')
            ->where(
                $qb->expr()->in('id', ':ids')
            )
            ->set('retries', 'retries + 1')
            ->set('date_modified', ':date_modified')
            ->setParameter('ids', $idList, ArrayParameterType::INTEGER)
            ->setParameter('date_modified', (new \DateTimeImmutable())->format(DateTimeHelper::FORMAT_DB))
            ->executeStatement();
    }

    /**
     * Check if there is webhook to process.
     */
    public function exists(int $id): bool
    {
        $qb     = $this->_em->getConnection()->createQueryBuilder();
        $result = $qb->select($this->getTableAlias().'.id')
            ->from(MAILVOTECH_TABLE_PREFIX.'webhook_queue', $this->getTableAlias())
            ->where($this->getTableAlias().'.webhook_id = :id')
            ->setParameter('id', $id)
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchOne();

        return (bool) $result;
    }
}
