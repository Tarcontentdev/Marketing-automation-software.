<?php

namespace MailVotech\SmsBundle\EventListener;

use MailVotech\LeadBundle\Event\ContactIdentificationEvent;
use MailVotech\LeadBundle\LeadEvents;
use MailVotech\SmsBundle\Entity\Stat;
use MailVotech\SmsBundle\Entity\StatRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class TrackingSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private StatRepository $statRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LeadEvents::ON_CLICKTHROUGH_IDENTIFICATION => ['onIdentifyContact', 0],
        ];
    }

    public function onIdentifyContact(ContactIdentificationEvent $event): void
    {
        $clickthrough = $event->getClickthrough();

        // Nothing left to identify by so stick to the tracked lead
        if (empty($clickthrough['channel']['sms']) && empty($clickthrough['stat'])) {
            return;
        }

        /** @var Stat $stat */
        $stat = $this->statRepository->findOneBy(['trackingHash' => $clickthrough['stat']]);

        if (!$stat) {
            // Stat doesn't exist so use the tracked lead
            return;
        }

        if ($stat->getSms() && (int) $stat->getSms()->getId() !== (int) $clickthrough['channel']['sms']) {
            // ID mismatch - fishy so use tracked lead
            return;
        }

        if (!$contact = $stat->getLead()) {
            return;
        }

        $event->setIdentifiedContact($contact, 'sms');
    }
}
