<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Internal\Executioner;

use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Order\ObjectChangeDAO;

interface FieldValidatorInterface
{
    /**
     * @param ObjectChangeDAO[] $changedObjects
     */
    public function validateFields(string $objectName, array $changedObjects): void;
}
