<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Sync\Notification\Handler;

use Doctrine\ORM\EntityManagerInterface;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Order\NotificationDAO;
use MailVotech\IntegrationsBundle\Sync\Notification\Helper\UserSummaryNotificationHelper;
use MailVotech\IntegrationsBundle\Sync\Notification\Writer;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Internal\Object\Contact;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\MailVotechSyncDataExchange;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Entity\LeadEventLog;
use MailVotech\LeadBundle\Entity\LeadEventLogRepository;

final class ContactNotificationHandler implements HandlerInterface
{
    private ?string $integrationDisplayName = null;

    private ?string $objectDisplayName = null;

    public function __construct(
        private readonly Writer $writer,
        private readonly LeadEventLogRepository $leadEventRepository,
        private readonly EntityManagerInterface $em,
        private readonly UserSummaryNotificationHelper $userNotificationHelper,
    ) {
    }

    public function getIntegration(): string
    {
        return MailVotechSyncDataExchange::NAME;
    }

    public function getSupportedObject(): string
    {
        return Contact::NAME;
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     */
    public function writeEntry(NotificationDAO $notificationDAO, string $integrationDisplayName, string $objectDisplayName): void
    {
        $this->integrationDisplayName = $integrationDisplayName;
        $this->objectDisplayName      = $objectDisplayName;

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

        $this->writeEventLogEntry($notificationDAO->getIntegration(), $notificationDAO->getMailVotechObjectId(), $notificationDAO->getMessage());

        // Store these so we can send one notice to the user
        $this->userNotificationHelper->storeSummaryNotification($integrationDisplayName, $objectDisplayName, $notificationDAO->getMailVotechObjectId());
    }

    public function finalize(): void
    {
        $this->userNotificationHelper->writeNotifications(
            Contact::NAME,
            'mailvotech.integration.sync.user_notification.contact_message'
        );
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     */
    private function writeEventLogEntry(string $integration, int $contactId, string $message): void
    {
        $eventLog = new LeadEventLog();
        $eventLog
            ->setLead($this->em->getReference(Lead::class, $contactId))
            ->setBundle('integrations')
            ->setObject($integration)
            ->setAction('sync')
            ->setProperties(
                [
                    'message'     => $message,
                    'integration' => $this->integrationDisplayName,
                    'object'      => $this->objectDisplayName,
                ]
            );

        $this->leadEventRepository->saveEntity($eventLog);
        $this->leadEventRepository->detachEntity($eventLog);
    }
}
