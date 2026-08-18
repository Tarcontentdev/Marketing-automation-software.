<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Internal\Executioner;

use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Order\ObjectChangeDAO;

interface ReferenceResolverInterface
{
    /**
     * @param ObjectChangeDAO[] $changedObjects
     */
    public function resolveReferences(string $objectName, array $changedObjects): void;
}
