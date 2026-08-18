<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Sync\SyncService;

use MailVotech\IntegrationsBundle\Sync\DAO\Sync\InputOptionsDAO;

interface SyncServiceInterface
{
    public function processIntegrationSync(InputOptionsDAO $inputOptionsDAO);
}
