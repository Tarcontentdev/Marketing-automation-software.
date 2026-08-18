<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Sync\Notification\Helper;

use MailVotech\IntegrationsBundle\Sync\Exception\ObjectNotSupportedException;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class UserNotificationBuilder
{
    public function __construct(
        private UserHelper $userHelper,
        private OwnerProvider $ownerProvider,
        private RouteHelper $routeHelper,
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * @return int[]
     *
     * @throws ObjectNotSupportedException
     */
    public function getUserIds(string $mailvotechObject, int $id): array
    {
        $owners = $this->ownerProvider->getOwnersForObjectIds($mailvotechObject, [$id]);

        if (!empty($owners[0]['owner_id'])) {
            return [(int) $owners[0]['owner_id']];
        }

        return $this->userHelper->getAdminUsers();
    }

    public function buildLink(string $mailvotechObject, int $id, string $linkText): string
    {
        return $this->routeHelper->getLink($mailvotechObject, $id, $linkText);
    }

    public function formatHeader(string $integrationDisplayName, string $objectDisplayName): string
    {
        return $this->translator->trans(
            'mailvotech.integration.sync.user_notification.header',
            [
                '%integration%' => $integrationDisplayName,
                '%object%'      => $objectDisplayName,
            ]
        );
    }

    public function formatMessage(string $message, string $link): string
    {
        return $this->translator->trans(
            'mailvotech.integration.sync.user_notification.sync_error',
            [
                '%name%'    => $link,
                '%message%' => $message,
            ]
        );
    }
}
