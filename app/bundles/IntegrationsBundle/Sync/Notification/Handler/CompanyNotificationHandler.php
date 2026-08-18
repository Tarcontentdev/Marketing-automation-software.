<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Sync\Notification\Handler;

use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Order\NotificationDAO;
use MailVotech\IntegrationsBundle\Sync\Notification\Helper\CompanyHelper;
use MailVotech\IntegrationsBundle\Sync\Notification\Helper\UserNotificationHelper;
use MailVotech\IntegrationsBundle\Sync\Notification\Writer;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\MailVotechSyncDataExchange;

final readonly class CompanyNotificationHandler implements HandlerInterface
{
    public function __construct(
        private Writer $writer,
        private UserNotificationHelper $userNotificationHelper,
        private CompanyHelper $companyHelper,
    ) {
    }

    public function getIntegration(): string
    {
        return MailVotechSyncDataExchange::NAME;
    }

    public function getSupportedObject(): string
    {
        return MailVotechSyncDataExchange::OBJECT_COMPANY;
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \MailVotech\IntegrationsBundle\Sync\Exception\ObjectNotSupportedException
     */
    public function writeEntry(NotificationDAO $notificationDAO, string $integrationDisplayName, string $objectDisplayName): void
    {
        $this->writer->writeAuditLogEntry(
            $notificationDAO->getIntegration(),
            $notificationDAO->getMailVotechObject(),
            $notificationDAO->getMailVotechObjectId(),
            'sync',
            [
                'integrationObject'   => $notificationDAO->getIntegrationObject(),
                'integrationObjectId' => $notificationDAO->getIntegrationObjectId(),
                'message'             => $notificationDAO->getMessage(),
            ]
        );

        $this->userNotificationHelper->writeNotification(
            $notificationDAO->getMessage(),
            $integrationDisplayName,
            $objectDisplayName,
            $notificationDAO->getMailVotechObject(),
            $notificationDAO->getMailVotechObjectId(),
            (string) $this->companyHelper->getCompanyName($notificationDAO->getMailVotechObjectId())
        );
    }

    public function finalize(): void
    {
        // Nothing to do
    }
}
