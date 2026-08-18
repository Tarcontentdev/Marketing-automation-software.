<?php

namespace MailVotech\AssetBundle\EventListener;

use MailVotech\AssetBundle\AssetEvents;
use MailVotech\AssetBundle\Entity\Asset;
use MailVotech\AssetBundle\Event\AssetLoadEvent;
use MailVotech\AssetBundle\Form\Type\CampaignEventAssetDownloadType;
use MailVotech\CampaignBundle\CampaignEvents;
use MailVotech\CampaignBundle\Event\CampaignBuilderEvent;
use MailVotech\CampaignBundle\Event\CampaignExecutionEvent;
use MailVotech\CampaignBundle\Executioner\RealTimeExecutioner;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class CampaignSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RealTimeExecutioner $realTimeExecutioner,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CampaignEvents::CAMPAIGN_ON_BUILD         => ['onCampaignBuild', 0],
            AssetEvents::ASSET_ON_LOAD                => ['onAssetDownload', 0],
            AssetEvents::ON_CAMPAIGN_TRIGGER_DECISION => ['onCampaignTriggerDecision', 0],
        ];
    }

    public function onCampaignBuild(CampaignBuilderEvent $event): void
    {
        $trigger = [
            'label'          => 'mailvotech.asset.campaign.event.download',
            'description'    => 'mailvotech.asset.campaign.event.download_descr',
            'eventName'      => AssetEvents::ON_CAMPAIGN_TRIGGER_DECISION,
            'formType'       => CampaignEventAssetDownloadType::class,
            'channel'        => 'asset',
            'channelIdField' => 'assets',
        ];

        $event->addDecision('asset.download', $trigger);
    }

    /**
     * Trigger point actions for asset download.
     */
    public function onAssetDownload(AssetLoadEvent $event): void
    {
        $asset = $event->getRecord()->getAsset();

        if (null !== $asset) {
            $this->realTimeExecutioner->execute('asset.download', $asset, 'asset', $asset->getId());
        }
    }

    public function onCampaignTriggerDecision(CampaignExecutionEvent $event): void
    {
        $eventDetails = $event->getEventDetails();

        if (null == $eventDetails) {
            $event->setResult(true);

            return;
        }

        if (!$eventDetails instanceof Asset) {
            $event->setResult(false);

            return;
        }

        $assetId       = $eventDetails->getId();
        $limitToAssets = $event->getConfig()['assets'];

        if (!empty($limitToAssets) && !in_array($assetId, $limitToAssets)) {
            $event->setResult(false);

            return;
        }

        $event->setResult(true);
    }
}
