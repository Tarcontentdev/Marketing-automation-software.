<?php

namespace MailVotech\DynamicContentBundle\Entity;

use Doctrine\ORM\Tools\Pagination\Paginator;
use MailVotech\CoreBundle\Entity\CommonRepository;
use MailVotech\CoreBundle\Helper\Serializer;
use MailVotech\ProjectBundle\Entity\ProjectRepositoryTrait;

/**
 * @extends CommonRepository<DynamicContent>
 */
final class DynamicContentRepository extends CommonRepository
{
    use ProjectRepositoryTrait;

    /**
     * Get a list of entities.
     *
     * @return Paginator
     */
    public function getEntities(array $args = [])
    {
        $q = $this->_em
            ->createQueryBuilder()
            ->select('e')
            ->from(DynamicContent::class, 'e', 'e.id');

        if (empty($args['iterable_mode'])) {
            $q->leftJoin('e.category', 'c');
        }

        $args['qb'] = $q;

        return parent::getEntities($args);
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder|\Doctrine\DBAL\Query\QueryBuilder $q
     */
    protected function addSearchCommandWhereClause($q, $filter): array
    {
        [$expr, $parameters] = $this->addStandardSearchCommandWhereClause($q, $filter);
        if ($expr) {
            return [$expr, $parameters];
        }

        [$expr, $parameters] = parent::addSearchCommandWhereClause($q, $filter);
        if ($expr) {
            return [$expr, $parameters];
        }

        $command         = $filter->command;
        $unique          = $this->generateRandomParameterName();

        switch ($command) {
            case $this->translator->trans('mailvotech.core.searchcommand.lang'):
                $langUnique      = $this->generateRandomParameterName();
                $langValue       = $filter->string.'_%';
                $forceParameters = [
                    $langUnique => $langValue,
                    $unique     => $filter->string,
                ];
                $expr = $q->expr()->or(
                    $q->expr()->eq('e.language', ":{$unique}"),
                    $q->expr()->like('e.language', ":{$langUnique}")
                );
                break;
            case $this->translator->trans('mailvotech.project.searchcommand.name'):
            case $this->translator->trans('mailvotech.project.searchcommand.name', [], null, 'en_US'):
                return $this->handleProjectFilter(
                    $this->_em->getConnection()->createQueryBuilder(),
                    'dynamic_content_id',
                    'dynamic_content_projects_xref',
                    $this->getTableAlias(),
                    $filter->string,
                    $filter->not
                );
        }

        if ($expr && $filter->not) {
            $expr = $q->expr()->not($expr);
        }

        if (!empty($forceParameters)) {
            $parameters = $forceParameters;
        }

        return [$expr, $parameters];
    }

    /**
     * @return string[]
     */
    public function getSearchCommands(): array
    {
        $commands = [
            'mailvotech.core.searchcommand.ispublished',
            'mailvotech.core.searchcommand.isunpublished',
            'mailvotech.core.searchcommand.isuncategorized',
            'mailvotech.core.searchcommand.ismine',
            'mailvotech.core.searchcommand.category',
            'mailvotech.core.searchcommand.lang',
            'mailvotech.project.searchcommand.name',
        ];

        return array_merge($commands, parent::getSearchCommands());
    }

    /**
     * @return array<array<string>>
     */
    protected function getDefaultOrder(): array
    {
        return [
            ['e.name', 'ASC'],
        ];
    }

    public function getTableAlias(): string
    {
        return 'e';
    }

    /**
     * Up the sent counts.
     *
     * @param int $increaseBy
     */
    public function upSentCount($id, $increaseBy = 1): void
    {
        $q = $this->_em->getConnection()->createQueryBuilder();

        $q->update(MAILVOTECH_TABLE_PREFIX.'dynamic_content')
            ->set('sent_count', 'sent_count + '.(int) $increaseBy)
            ->where('id = '.(int) $id);

        $q->executeStatement();
    }

    /**
     * @param string $search
     * @param int    $limit
     * @param int    $start
     * @param bool   $viewOther
     * @param bool   $topLevel
     * @param array  $ignoreIds
     * @param string $where
     *
     * @return array
     */
    public function getDynamicContentList($search = '', $limit = 10, $start = 0, $viewOther = false, $topLevel = false, $ignoreIds = [], $where = null)
    {
        $q = $this->createQueryBuilder('e');
        $q->select('partial e.{id, name, language}');

        if (!empty($search)) {
            if (is_array($search)) {
                $search = array_map(intval(...), $search);
                $q->andWhere($q->expr()->in('e.id', ':search'))
                  ->setParameter('search', $search);
            } else {
                $q->andWhere($q->expr()->like('e.name', ':search'))
                  ->setParameter('search', "%{$search}%");
            }
        }

        if (!$viewOther) {
            $q->andWhere($q->expr()->eq('e.createdBy', ':id'))
                ->setParameter('id', $this->currentUser->getId());
        }

        if ('translation' == $topLevel) {
            // only get top level pages
            $q->andWhere($q->expr()->isNull('e.translationParent'));
        } elseif ('variant' == $topLevel) {
            $q->andWhere($q->expr()->isNull('e.variantParent'));
        }

        if (!empty($ignoreIds)) {
            $q->andWhere($q->expr()->notIn('e.id', ':dwc_ids'))
                ->setParameter('dwc_ids', $ignoreIds);
        }

        if ($where) {
            $q->andWhere($where);
        }

        $q->orderBy('e.name');

        if (!empty($limit)) {
            $q->setFirstResult($start)
                ->setMaxResults($limit);
        }

        return $q->getQuery()->getArrayResult();
    }

    public function getDynamicContentForSlotFromCampaign($slot): DynamicContent|false
    {
        $qb = $this->_em->getConnection()->createQueryBuilder();

        $qb->select('ce.properties')
            ->from(MAILVOTECH_TABLE_PREFIX.'campaign_events', 'ce')
            ->leftJoin('ce', MAILVOTECH_TABLE_PREFIX.'campaigns', 'c', 'c.id = ce.campaign_id')
            ->andWhere($qb->expr()->eq('ce.type', $qb->expr()->literal('dwc.decision')))
            ->andWhere($qb->expr()->like('ce.properties', ':slot'))
            ->setParameter('slot', '%'.$slot.'%')
            ->orderBy('c.is_published');

        $result = $qb->executeQuery()->fetchAllAssociative();

        foreach ($result as $item) {
            $properties = Serializer::decode($item['properties']);

            if (isset($properties['dynamicContent'])) {
                $dwc = $this->getEntity($properties['dynamicContent']);

                if ($dwc instanceof DynamicContent) {
                    return $dwc;
                }
            }
        }

        return false;
    }
}
