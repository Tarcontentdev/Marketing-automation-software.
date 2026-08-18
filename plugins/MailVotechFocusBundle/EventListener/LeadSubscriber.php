<?php

namespace MailVotechPlugin\MailVotechFocusBundle\EventListener;

use MailVotech\CoreBundle\Translation\Translator;
use MailVotech\LeadBundle\Event\LeadTimelineEvent;
use MailVotech\LeadBundle\LeadEvents;
use MailVotechPlugin\MailVotechFocusBundle\Entity\Stat;
use MailVotechPlugin\MailVotechFocusBundle\FocusEventTypes;
use MailVotechPlugin\MailVotechFocusBundle\Model\FocusModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\RouterInterface;

final readonly class LeadSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Translator $translator,
        private RouterInterface $router,
        private FocusModel $focusModel,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LeadEvents::TIMELINE_ON_GENERATE      => ['onTimelineGenerate', 0],
        ];
    }

    /**
     * Compile events for the lead timeline.
     */
    public function onTimelineGenerate(LeadTimelineEvent $event): void
    {
        $eventViewTypeName = $this->translator->trans('mailvotech.focus.event.view');
        $event->addEventType(FocusEventTypes::FOCUS_ON_VIEW, $eventViewTypeName);
        $eventViewApplicable = $event->isApplicable(FocusEventTypes::FOCUS_ON_VIEW);

        $eventClickTypeName = $this->translator->trans('mailvotech.focus.event.click');
        $event->addEventType(FocusEventTypes::FOCUS_ON_CLICK, $eventClickTypeName);
        $eventClickApplicable = $event->isApplicable(FocusEventTypes::FOCUS_ON_CLICK);

        $event->addSerializerGroup('focusList');

        $leadId = $event->getLeadId();

        $statsViewsByLead = $this->focusModel->getStatRepository()->getStatsViewByLead($leadId, $event->getQueryOptions());
        $statsClickByLead = $this->focusModel->getStatRepository()->getStatsClickByLead($leadId, $event->getQueryOptions());

        if (!$event->isEngagementCount()) {
            $icon     = 'ri-search-line';

            // Add the view to the event array
            foreach (array_merge($statsViewsByLead['results'] ?? [], $statsClickByLead['results'] ?? []) as $statsView) {
                if (((Stat::TYPE_CLICK == $statsView['type']) && $eventClickApplicable)
                    || ((Stat::TYPE_NOTIFICATION == $statsView['type']) && $eventViewApplicable)) {
                    $eventLabel = [
                        'label' => $statsView['focus_name'],
                        'href'  => $this->router->generate('mailvotech_focus_action', ['objectAction' => 'view', 'objectId' => $statsView['focus_id']]),
                    ];

                    $eventType = (Stat::TYPE_NOTIFICATION == $statsView['type']) ? FocusEventTypes::FOCUS_ON_VIEW : FocusEventTypes::FOCUS_ON_CLICK;

                    $event->addEvent(
                        [
                            'event'           => $eventType,
                            'eventId'         => $eventType.'.'.$statsView['id'],
                            'eventLabel'      => $eventLabel,
                            'eventType'       => (Stat::TYPE_NOTIFICATION == $statsView['type']) ? $eventViewTypeName : $eventClickTypeName,
                            'timestamp'       => $statsView['date_added'],
                            'icon'            => $icon,
                            'contactId'       => $leadId,
                        ]
                    );
                }
            }
            // Add to counter view
            $event->addToCounter(FocusEventTypes::FOCUS_ON_VIEW, $statsViewsByLead['total'] ?? 0);
            // Add to counter click
            $event->addToCounter(FocusEventTypes::FOCUS_ON_CLICK, $statsClickByLead['total'] ?? 0);
        }
    }
}
