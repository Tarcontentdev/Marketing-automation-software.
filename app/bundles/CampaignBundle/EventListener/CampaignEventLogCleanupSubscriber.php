<?php

declare(strict_types=1);

namespace MailVotech\CampaignBundle\EventListener;

use MailVotech\CampaignBundle\CampaignEvents;
use MailVotech\CampaignBundle\Entity\FailedLeadEventLogRepository;
use MailVotech\CampaignBundle\Entity\LeadEventLog;
use MailVotech\CampaignBundle\Event\ExecutedBatchEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class CampaignEventLogCleanupSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private FailedLeadEventLogRepository $failedLeadEventLogRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CampaignEvents::ON_EVENT_EXECUTED_BATCH => ['onEventBatchExecuted', -100],
        ];
    }

    /**
     * Deletes failed log entries for all successful event logs.
     */
    public function onEventBatchExecuted(ExecutedBatchEvent $event): void
    {
        $ids = $event->getExecuted()
            ->map(fn (LeadEventLog $eventLog): int => $eventLog->getId())
            ->getValues();

        if (!$ids) {
            return;
        }

        $this->failedLeadEventLogRepository->deleteByIds($ids);
    }
}
