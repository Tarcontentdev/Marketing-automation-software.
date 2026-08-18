<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Sync\SyncDataExchange;

use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Order\OrderDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Report\ReportDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Request\RequestDAO;

interface SyncDataExchangeInterface
{
    /**
     * Sync to integration.
     */
    public function getSyncReport(RequestDAO $requestDAO): ReportDAO;

    /**
     * Sync from integration.
     */
    public function executeSyncOrder(OrderDAO $syncOrderDAO);
}
