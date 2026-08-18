<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Sync\Notification;

use Doctrine\ORM\EntityManagerInterface;
use MailVotech\CoreBundle\Service\BulkNotificationInterface;
use MailVotech\IntegrationsBundle\Sync\Notification\Helper\UserNotificationBuilder;
use MailVotech\UserBundle\Entity\User;

class BulkNotification
{
    public function __construct(
        private readonly BulkNotificationInterface $bulkNotification,
        private readonly UserNotificationBuilder $userNotificationBuilder,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function addNotification(
        string $deduplicateValue,
        string $message,
        string $integrationDisplayName,
        string $objectDisplayName,
        string $mailvotechObject,
        int $id,
        string $linkText,
    ): void {
        $link    = $this->userNotificationBuilder->buildLink($mailvotechObject, $id, $linkText);
        $userIds = $this->userNotificationBuilder->getUserIds($mailvotechObject, $id);

        foreach ($userIds as $userId) {
            /** @var User $user */
            $user = $this->entityManager->getReference(User::class, $userId);
            $this->bulkNotification->addNotification(
                $deduplicateValue,
                $this->userNotificationBuilder->formatMessage($message, $link),
                null,
                $this->userNotificationBuilder->formatHeader($integrationDisplayName, $objectDisplayName),
                'ri-refresh-line',
                null,
                $user
            );
        }
    }

    /**
     * @param \DateTime|null $deduplicateDateTimeFrom If last 24 hours for deduplication does not fit, change it here
     */
    public function flush(?\DateTime $deduplicateDateTimeFrom = null): void
    {
        $this->bulkNotification->flush($deduplicateDateTimeFrom);
    }
}
