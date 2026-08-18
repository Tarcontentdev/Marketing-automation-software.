<?php

declare(strict_types=1);

namespace MailVotech\PointBundle\Entity;

use MailVotech\CoreBundle\Entity\CommonRepository;

/**
 * @extends CommonRepository<PointInsight>
 */
final class PointInsightRepository extends CommonRepository
{
    public function getEntities(array $args = [])
    {
        $q = $this->_em
            ->createQueryBuilder()
            ->select($this->getTableAlias().', cat')
            ->from(PointInsight::class, $this->getTableAlias())
            ->leftJoin($this->getTableAlias().'.category', 'cat');

        $args['qb'] = $q;

        return parent::getEntities($args);
    }

    public function getTableAlias(): string
    {
        return 'pi';
    }
}
