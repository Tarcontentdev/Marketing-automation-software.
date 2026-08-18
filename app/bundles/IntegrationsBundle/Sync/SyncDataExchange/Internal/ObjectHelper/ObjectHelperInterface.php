<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Internal\ObjectHelper;

use MailVotech\IntegrationsBundle\Entity\ObjectMapping;
use MailVotech\IntegrationsBundle\Sync\DAO\Mapping\UpdatedObjectMappingDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Order\ObjectChangeDAO;

interface ObjectHelperInterface
{
    /**
     * @param ObjectChangeDAO[] $objects
     *
     * @return ObjectMapping[]
     */
    public function create(array $objects): array;

    /**
     * @param ObjectChangeDAO[] $objects
     *
     * @return UpdatedObjectMappingDAO[]
     */
    public function update(array $ids, array $objects): array;

    /**
     * @param int $start
     * @param int $limit
     */
    public function findObjectsBetweenDates(\DateTimeInterface $from, \DateTimeInterface $to, $start, $limit): array;

    public function findObjectsByIds(array $ids): array;

    public function findObjectsByFieldValues(array $fields): array;
}
