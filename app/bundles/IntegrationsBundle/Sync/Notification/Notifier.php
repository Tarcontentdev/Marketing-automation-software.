<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Sync\Notification;

use MailVotech\IntegrationsBundle\Exception\IntegrationNotFoundException;
use MailVotech\IntegrationsBundle\Helper\ConfigIntegrationsHelper;
use MailVotech\IntegrationsBundle\Helper\SyncIntegrationsHelper;
use MailVotech\IntegrationsBundle\Integration\Interfaces\ConfigFormSyncInterface;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Order\NotificationDAO;
use MailVotech\IntegrationsBundle\Sync\Exception\HandlerNotSupportedException;
use MailVotech\IntegrationsBundle\Sync\Notification\Handler\HandlerContainer;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\MailVotechSyncDataExchange;
use Symfony\Contracts\Translation\TranslatorInterface;

class Notifier
{
    public function __construct(
        private readonly HandlerContainer $handlerContainer,
        private readonly SyncIntegrationsHelper $syncIntegrationsHelper,
        private readonly ConfigIntegrationsHelper $configIntegrationsHelper,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * @param NotificationDAO[] $notifications
     *
     * @throws HandlerNotSupportedException
     * @throws IntegrationNotFoundException
     */
    public function noteMailVotechSyncIssue(array $notifications, string $integrationHandler = MailVotechSyncDataExchange::NAME): void
    {
        foreach ($notifications as $notification) {
            $handler = $this->handlerContainer->getHandler($integrationHandler, $notification->getMailVotechObject());

            $integrationDisplayName = $this->syncIntegrationsHelper->getIntegration($notification->getIntegration())->getDisplayName();
            $objectDisplayName      = $this->getObjectDisplayName($notification->getIntegration(), $notification->getIntegrationObject());

            $handler->writeEntry($notification, $integrationDisplayName, $objectDisplayName);
        }
    }

    /**
     * Finalizes notifications such as pushing summary entries to the user notifications.
     */
    public function finalizeNotifications(): void
    {
        foreach ($this->handlerContainer->getHandlers() as $handler) {
            $handler->finalize();
        }
    }

    private function getObjectDisplayName(string $integration, string $object): string
    {
        try {
            $configIntegration = $this->configIntegrationsHelper->getIntegration($integration);
        } catch (IntegrationNotFoundException) {
            return ucfirst($object);
        }

        if (!$configIntegration instanceof ConfigFormSyncInterface) {
            return ucfirst($object);
        }

        $objects = $configIntegration->getSyncConfigObjects();

        if (!isset($objects[$object])) {
            return ucfirst($object);
        }

        return $this->translator->trans($objects[$object]);
    }
}
