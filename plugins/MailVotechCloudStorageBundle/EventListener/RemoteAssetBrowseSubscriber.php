<?php

namespace MailVotechPlugin\MailVotechCloudStorageBundle\EventListener;

use MailVotech\AssetBundle\AssetEvents;
use MailVotech\AssetBundle\Event as Events;
use MailVotechPlugin\MailVotechCloudStorageBundle\Exception\InvalidCredentialConfigurationException;
use MailVotechPlugin\MailVotechCloudStorageBundle\Integration\CloudStorageIntegration;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class RemoteAssetBrowseSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            AssetEvents::ASSET_ON_REMOTE_BROWSE => ['onAssetRemoteBrowse', 0],
        ];
    }

    /**
     * Fetches the connector for an event's integration.
     */
    public function onAssetRemoteBrowse(Events\RemoteAssetBrowseEvent $event): void
    {
        /** @var CloudStorageIntegration $integration */
        $integration = $event->getIntegration();

        try {
            $event->setAdapter($integration->getAdapter());
        } catch (InvalidCredentialConfigurationException $e) {
            $event->setFailed($e->getMessage());
        }
    }
}
