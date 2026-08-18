<?php

namespace MailVotech\EmailBundle\EventListener;

use MailVotech\EmailBundle\Entity\EmailReplyRepository;
use MailVotech\EmailBundle\Entity\StatRepository;
use MailVotech\LeadBundle\Event\LeadMergeEvent;
use MailVotech\LeadBundle\Event\LeadTimelineEvent;
use MailVotech\LeadBundle\LeadEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class LeadSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EmailReplyRepository $emailReplyRepository,
        private StatRepository $statRepository,
        private TranslatorInterface $translator,
        private RouterInterface $router,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LeadEvents::TIMELINE_ON_GENERATE => ['onTimelineGenerate', 0],
            LeadEvents::LEAD_POST_MERGE      => ['onLeadMerge', 0],
        ];
    }

    /**
     * Compile events for the lead timeline.
     */
    public function onTimelineGenerate(LeadTimelineEvent $event): void
    {
        $this->addEmailEvents($event, 'read');
        $this->addEmailEvents($event, 'sent');
        $this->addEmailEvents($event, 'failed');
        $this->addEmailReplies($event);
    }

    public function onLeadMerge(LeadMergeEvent $event): void
    {
        $this->statRepository->updateLead(
            $event->getLoser()->getId(),
            $event->getVictor()->getId()
        );
    }

    private function addEmailEvents(LeadTimelineEvent $event, string $state): void
    {
        // Set available event types
        $eventTypeKey  = 'email.'.$state;
        $eventTypeName = $this->translator->trans('mailvotech.email.'.$state);
        $event->addEventType($eventTypeKey, $eventTypeName);
        $event->addSerializerGroup('emailList');

        // Decide if those events are filtered
        if (!$event->isApplicable($eventTypeKey)) {
            return;
        }

        $queryOptions          = $event->getQueryOptions();
        $queryOptions['state'] = $state;
        $stats                 = $this->statRepository->getLeadStats($event->getLeadId(), $queryOptions);

        // Add total to counter
        $event->addToCounter($eventTypeKey, $stats);

        if (!$event->isEngagementCount()) {
            // Add the events to the event array
            foreach ($stats['results'] as $stat) {
                if (!empty($stat['email_name'])) {
                    $label = $stat['email_name'];
                } elseif (!empty($stat['storedSubject'])) {
                    $label = $this->translator->trans('mailvotech.email.timeline.event.custom_email').': '.$stat['storedSubject'];
                } else {
                    $label = $this->translator->trans('mailvotech.email.timeline.event.custom_email');
                }

                if (!empty($stat['idHash'])) {
                    $eventName = [
                        'label'      => $label,
                        'href'       => $this->router->generate('mailvotech_email_webview', ['idHash' => $stat['idHash']]),
                        'isExternal' => true,
                    ];
                } else {
                    $eventName = $label;
                }
                if ('failed' === $state || 'sent' === $state) { // this is to get the correct column for date dateSent
                    $dateSent = 'sent';
                } else {
                    $dateSent = 'read';
                }

                $contactId = $stat['lead_id'];
                unset($stat['lead_id']);
                $event->addEvent(
                    [
                        'event'      => $eventTypeKey,
                        'eventId'    => $eventTypeKey.$stat['id'],
                        'eventLabel' => $eventName,
                        'eventType'  => $eventTypeName,
                        'timestamp'  => $stat['date'.ucfirst($dateSent)],
                        'extra'      => [
                            'stat' => $stat,
                            'type' => $state,
                        ],
                        'contentTemplate' => '@MailVotechEmail/SubscribedEvents/Timeline/index.html.twig',
                        'icon'            => ('read' === $state) ? 'ri-mail-open-line' : 'ri-mail-unread-line',
                        'contactId'       => $contactId,
                    ]
                );
            }
        }
    }

    private function addEmailReplies(LeadTimelineEvent $event): void
    {
        $eventTypeKey  = 'email.replied';
        $eventTypeName = $this->translator->trans('mailvotech.email.replied');
        $event->addEventType($eventTypeKey, $eventTypeName);
        $event->addSerializerGroup('emailList');

        // Decide if those events are filtered
        if (!$event->isApplicable($eventTypeKey)) {
            return;
        }

        $options          = $event->getQueryOptions();
        $replies          = $this->emailReplyRepository->getByLeadIdForTimeline($event->getLeadId(), $options);
        if (!$event->isEngagementCount()) {
            foreach ($replies['results'] as $reply) {
                $label = $this->translator->trans('mailvotech.email.timeline.event.email_reply');
                if (!empty($reply['email_name'])) {
                    $label .= ': '.$reply['email_name'];
                } elseif (!empty($reply['storedSubject'])) {
                    $label .= ': '.$reply['storedSubject'];
                }

                $contactId = $reply['lead_id'];
                unset($reply['lead_id']);

                $event->addEvent(
                    [
                        'event'      => $eventTypeKey,
                        'eventId'    => $eventTypeKey.$reply['id'],
                        'eventLabel' => $label,
                        'eventType'  => $eventTypeName,
                        'timestamp'  => $reply['date_replied'],
                        'icon'       => 'ri-mail-unread-line',
                        'contactId'  => $contactId,
                    ]
                );
            }
        }
    }
}
