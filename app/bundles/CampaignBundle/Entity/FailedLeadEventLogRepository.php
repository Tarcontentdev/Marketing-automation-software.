<?php

declare(strict_types=1);

namespace MailVotech\CampaignBundle\Entity;

use Doctrine\DBAL\ArrayParameterType;
use MailVotech\CoreBundle\Entity\CommonRepository;

/**
 * @extends CommonRepository<FailedLeadEventLog>
 */
final class FailedLeadEventLogRepository extends CommonRepository
{
    /**
     * @param array<string|int> $ids
     */
    public function deleteByIds(array $ids): void
    {
        if ([] === $ids) {
            return;
        }

        $this->_em->getConnection()
            ->createQueryBuilder()
            ->delete(MAILVOTECH_TABLE_PREFIX.'campaign_lead_event_failed_log')
            ->where('log_id IN (:ids)')
            ->setParameter('ids', $ids, ArrayParameterType::STRING)
            ->executeStatement();
    }
}
