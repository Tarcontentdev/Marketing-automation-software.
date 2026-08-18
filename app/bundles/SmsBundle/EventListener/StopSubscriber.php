<?php

namespace MailVotech\SmsBundle\EventListener;

use MailVotech\LeadBundle\Entity\DoNotContact;
use MailVotech\LeadBundle\Model\DoNotContact as DoNotContactModel;
use MailVotech\SmsBundle\Event\ReplyEvent;
use MailVotech\SmsBundle\SmsEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class StopSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private DoNotContactModel $doNotContactModel,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SmsEvents::ON_REPLY => ['onReply', 0],
        ];
    }

    public function onReply(ReplyEvent $event): void
    {
        $message = $event->getMessage();

        if ('stop' === strtolower($message)) {
            // Unsubscribe the contact
            $this->doNotContactModel->addDncForContact($event->getContact()->getId(), 'sms', DoNotContact::UNSUBSCRIBED);
        }
    }
}
