<?php

namespace MailVotech\AssetBundle\EventListener;

use MailVotech\AssetBundle\Entity\DownloadRepository;
use MailVotech\AssetBundle\Model\AssetModel;
use MailVotech\LeadBundle\Event\LeadChangeEvent;
use MailVotech\LeadBundle\Event\LeadMergeEvent;
use MailVotech\LeadBundle\Event\LeadTimelineEvent;
use MailVotech\LeadBundle\LeadEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class LeadSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private AssetModel $assetModel,
        private TranslatorInterface $translator,
        private RouterInterface $router,
        private DownloadRepository $downloadRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LeadEvents::TIMELINE_ON_GENERATE => ['onTimelineGenerate', 0],
            LeadEvents::CURRENT_LEAD_CHANGED => ['onLeadChange', 0],
            LeadEvents::LEAD_POST_MERGE      => ['onLeadMerge', 0],
        ];
    }

    /**
     * Compile events for the lead timeline.
     */
    public function onTimelineGenerate(LeadTimelineEvent $event): void
    {
        // Set available event types
        $eventTypeKey  = 'asset.download';
        $eventTypeName = $this->translator->trans('mailvotech.asset.event.download');
        $event->addEventType($eventTypeKey, $eventTypeName);
        $event->addSerializerGroup('assetList');

        // Decide if those events are filtered
        if (!$event->isApplicable($eventTypeKey)) {
            return;
        }

        $downloads = $this->downloadRepository->getLeadDownloads($event->getLeadId(), $event->getQueryOptions());

        // Add total number to counter
        $event->addToCounter($eventTypeKey, $downloads);

        if (!$event->isEngagementCount()) {
            // Add the downloads to the event array
            foreach ($downloads['results'] as $download) {
                $asset    = $download['asset_id'] ? $this->assetModel->getEntity($download['asset_id']) : null;
                $hasAsset = $asset && $asset->getId();

                $eventLabel = $hasAsset
                    ? [
                        'label' => $download['title'],
                        'href'  => $this->router->generate('mailvotech_asset_action', ['objectAction' => 'view', 'objectId' => $download['asset_id']]),
                    ]
                    : (string) ($download['title'] ?? $this->translator->trans('mailvotech.asset.asset.deleted'));

                $event->addEvent(
                    [
                        'event'      => $eventTypeKey,
                        'eventId'    => $eventTypeKey.$download['download_id'],
                        'eventLabel' => $eventLabel,
                        'extra'      => [
                            'asset'            => $hasAsset ? $asset : null,
                            'assetDownloadUrl' => $hasAsset ? $this->assetModel->generateUrl($asset) : null,
                        ],
                        'eventType'       => $eventTypeName,
                        'timestamp'       => $download['dateDownload'],
                        'icon'            => 'ri-download-line',
                        'contentTemplate' => '@MailVotechAsset/SubscribedEvents/Timeline/index.html.twig',
                        'contactId'       => $download['lead_id'],
                    ]
                );
            }
        }
    }

    public function onLeadChange(LeadChangeEvent $event): void
    {
        $this->assetModel->getDownloadRepository()->updateLeadByTrackingId(
            $event->getNewLead()->getId(),
            $event->getNewTrackingId(),
            $event->getOldTrackingId()
        );
    }

    public function onLeadMerge(LeadMergeEvent $event): void
    {
        $this->assetModel->getDownloadRepository()->updateLead($event->getLoser()->getId(), $event->getVictor()->getId());
    }
}
