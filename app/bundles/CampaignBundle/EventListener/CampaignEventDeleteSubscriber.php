<?php

declare(strict_types=1);

namespace MailVotech\CampaignBundle\EventListener;

use MailVotech\CampaignBundle\CampaignEvents;
use MailVotech\CampaignBundle\Entity\LeadEventLogRepository;
use MailVotech\CampaignBundle\Event\DeleteCampaign;
use MailVotech\CampaignBundle\Event\DeleteEvent;
use MailVotech\CampaignBundle\Helper\CampaignConfig;
use MailVotech\CampaignBundle\Model\CampaignModel;
use MailVotech\CampaignBundle\Model\EventModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class CampaignEventDeleteSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LeadEventLogRepository $leadEventLogRepository,
        private CampaignConfig $campaignConfig,
        private CampaignModel $campaignModel,
        private EventModel $eventModel,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CampaignEvents::ON_CAMPAIGN_DELETE => ['onCampaignDelete', 0],
            CampaignEvents::ON_EVENT_DELETE    => ['onEventDelete', 0],
        ];
    }

    public function onCampaignDelete(DeleteCampaign $event): void
    {
        if ($this->campaignConfig->shouldDeleteEventLogInBackground()) {
            return;
        }

        $campaignId = $event->getCampaign()->getId();
        // For campaign deletion, remove both events and logs
        $this->leadEventLogRepository->removeEventLogsByCampaignId($campaignId);
        $this->eventModel->deleteEventsByCampaignId($campaignId);
        $this->campaignModel->deleteCampaign($event->getCampaign());
    }

    public function onEventDelete(DeleteEvent $event): void
    {
        if ($this->campaignConfig->shouldDeleteEventLogInBackground()) {
            return;
        }
        $eventIds = $event->getEventIds();
        // For individual event deletion, only soft-delete events but keep the logs
        $this->eventModel->deleteEventsByEventIds($eventIds);
    }
}
