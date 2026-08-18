<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Sync\Notification\Helper;

use Doctrine\ORM\ORMException;
use MailVotech\IntegrationsBundle\Sync\Exception\ObjectNotSupportedException;
use MailVotech\IntegrationsBundle\Sync\Notification\Writer;

final readonly class UserNotificationHelper
{
    public function __construct(
        private Writer $writer,
        private UserNotificationBuilder $userNotificationBuilder,
    ) {
    }

    /**
     * @throws ORMException
     * @throws ObjectNotSupportedException
     */
    public function writeNotification(
        string $message,
        string $integrationDisplayName,
        string $objectDisplayName,
        string $mailvotechObject,
        int $id,
        string $linkText,
        ?string $deduplicateValue = null,
        ?\DateTime $deduplicateDateTimeFrom = null,
    ): void {
        $link    = $this->userNotificationBuilder->buildLink($mailvotechObject, $id, $linkText);
        $userIds = $this->userNotificationBuilder->getUserIds($mailvotechObject, $id);

        foreach ($userIds as $userId) {
            $this->writer->writeUserNotification(
                $this->userNotificationBuilder->formatHeader($integrationDisplayName, $objectDisplayName),
                $this->userNotificationBuilder->formatMessage($message, $link),
                $userId,
                $deduplicateValue,
                $deduplicateDateTimeFrom
            );
        }
    }
}
