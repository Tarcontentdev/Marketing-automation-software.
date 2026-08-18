<?php

namespace MailVotech\PointBundle\EventListener;

use MailVotech\LeadBundle\Entity\PointsChangeLogRepository;
use MailVotech\LeadBundle\Event\LeadEvent;
use MailVotech\LeadBundle\Event\LeadMergeEvent;
use MailVotech\LeadBundle\Event\LeadTimelineEvent;
use MailVotech\LeadBundle\Event\PointsChangeEvent;
use MailVotech\LeadBundle\LeadEvents;
use MailVotech\PointBundle\Entity\LeadPointLogRepository;
use MailVotech\PointBundle\Entity\LeadTriggerLogRepository;
use MailVotech\PointBundle\Model\TriggerModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class LeadSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private TriggerModel $triggerModel,
        private TranslatorInterface $translator,
        private PointsChangeLogRepository $pointsChangeLogRepository,
        private LeadPointLogRepository $leadPointLogRepository,
        private LeadTriggerLogRepository $leadTriggerLogRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LeadEvents::LEAD_POINTS_CHANGE   => ['onLeadPointsChange', 0],
            LeadEvents::TIMELINE_ON_GENERATE => ['onTimelineGenerate', 0],
            LeadEvents::LEAD_POST_MERGE      => ['onLeadMerge', 0],
            LeadEvents::LEAD_POST_SAVE       => ['onLeadSave', -1],
        ];
    }

    /**
     * Trigger applicable events for the lead.
     */
    public function onLeadPointsChange(PointsChangeEvent $event): void
    {
        $this->triggerModel->triggerEvents($event->getLead());
    }

    /**
     * Handle point triggers for new leads (including 0 point triggers).
     */
    public function onLeadSave(LeadEvent $event): void
    {
        if ($event->isNew()) {
            $this->triggerModel->triggerEvents($event->getLead());
        }
    }

    /**
     * Compile events for the lead timeline.
     */
    public function onTimelineGenerate(LeadTimelineEvent $event): void
    {
        // Set available event types
        $eventTypeKey  = 'point.gained';
        $eventTypeName = $this->translator->trans('mailvotech.point.event.gained');
        $event->addEventType($eventTypeKey, $eventTypeName);
        $event->addSerializerGroup('pointList');

        if (!$event->isApplicable($eventTypeKey)) {
            return;
        }

        $logs = $this->pointsChangeLogRepository->getLeadTimelineEvents($event->getLeadId(), $event->getQueryOptions());

        // Add to counter
        $event->addToCounter($eventTypeKey, $logs);

        if (!$event->isEngagementCount()) {
            // Add the logs to the event array
            foreach ($logs['results'] as $log) {
                $eventLabel = $log['eventName'].' / '.$log['delta'];
                if (!empty($log['groupName'])) {
                    $eventLabel .= ' ('.$log['groupName'].')';
                }

                $event->addEvent(
                    [
                        'event'      => $eventTypeKey,
                        'eventId'    => $eventTypeKey.$log['id'],
                        'eventLabel' => $eventLabel,
                        'eventType'  => $eventTypeName,
                        'timestamp'  => $log['dateAdded'],
                        'extra'      => [
                            'log' => $log,
                        ],
                        'icon'      => 'ri-calculator-line',
                        'contactId' => $log['lead_id'],
                    ]
                );
            }
        }
    }

    public function onLeadMerge(LeadMergeEvent $event): void
    {
        $this->leadPointLogRepository->updateLead(
            $event->getLoser()->getId(),
            $event->getVictor()->getId()
        );

        $this->leadTriggerLogRepository->updateLead(
            $event->getLoser()->getId(),
            $event->getVictor()->getId()
        );
    }
}
