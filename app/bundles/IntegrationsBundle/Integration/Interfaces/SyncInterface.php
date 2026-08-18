<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Integration\Interfaces;

use MailVotech\IntegrationsBundle\Sync\DAO\Mapping\MappingManualDAO;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\SyncDataExchangeInterface;

interface SyncInterface extends IntegrationInterface
{
    public function getMappingManual(): MappingManualDAO;

    public function getSyncDataExchange(): SyncDataExchangeInterface;
}
