<?php

namespace MailVotech\PointBundle\Entity;

use Doctrine\DBAL\ArrayParameterType;
use MailVotech\CoreBundle\Entity\CommonRepository;

/**
 * @extends CommonRepository<LeadPointLog>
 */
final class LeadPointLogRepository extends CommonRepository
{
    /**
     * Updates lead ID (e.g. after a lead merge).
     */
    public function updateLead($fromLeadId, $toLeadId): void
    {
        // First check to ensure the $toLead doesn't already exist
        $results = $this->_em->getConnection()->createQueryBuilder()
            ->select('pl.point_id')
            ->from(MAILVOTECH_TABLE_PREFIX.'point_lead_action_log', 'pl')
            ->where('pl.lead_id = '.$toLeadId)
            ->executeQuery()
            ->fetchAllAssociative();

        $actions = [];
        foreach ($results as $r) {
            $actions[] = $r['point_id'];
        }

        $q = $this->_em->getConnection()->createQueryBuilder();
        $q->update(MAILVOTECH_TABLE_PREFIX.'point_lead_action_log')
            ->set('lead_id', (int) $toLeadId)
            ->where('lead_id = '.(int) $fromLeadId);

        if ([] !== $actions) {
            $q->andWhere(
                $q->expr()->notIn('point_id', ':actions')
            )
                ->setParameter('actions', $actions, ArrayParameterType::INTEGER)
                ->executeStatement();

            // Delete remaining leads as the new lead already belongs
            $this->_em->getConnection()->createQueryBuilder()
                ->delete(MAILVOTECH_TABLE_PREFIX.'point_lead_action_log')
                ->where('lead_id = '.(int) $fromLeadId)
                ->executeStatement();
        } else {
            $q->executeStatement();
        }
    }
}
