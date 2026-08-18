<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\EventListener;

use MailVotech\CoreBundle\Translation\Translator;
use MailVotech\LeadBundle\Entity\LeadEventLogRepository;
use MailVotech\LeadBundle\Event\LeadTimelineEvent;
use MailVotech\LeadBundle\LeadEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class TimelineEventLogSubscriber implements EventSubscriberInterface
{
    use TimelineEventLogTrait;

    public function __construct(
        Translator $translator,
        LeadEventLogRepository $leadEventLogRepository,
    ) {
        $this->translator         = $translator;
        $this->eventLogRepository = $leadEventLogRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LeadEvents::TIMELINE_ON_GENERATE => ['onTimelineGenerate', 0],
        ];
    }

    public function onTimelineGenerate(LeadTimelineEvent $event): void
    {
        $this->addEvents(
            $event,
            'lead.source.created',
            'mailvotech.lead.timeline.created_source',
            'ri-spy-line',
            null,
            null,
            'created_contact'
        );

        $this->addEvents(
            $event,
            'lead.source.identified',
            'mailvotech.lead.timeline.identified_source',
            'ri-user-6-fill',
            null,
            null,
            'identified_contact'
        );
    }
}
