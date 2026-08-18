<?php

namespace MailVotech\EmailBundle\EventListener;

use MailVotech\EmailBundle\EmailEvents;
use MailVotech\EmailBundle\Exception\EmailCouldNotBeSentException;
use MailVotech\EmailBundle\Model\SendEmailToUser;
use MailVotech\PointBundle\Event\TriggerExecutedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class EmailToUserSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private SendEmailToUser $sendEmailToUser,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [EmailEvents::ON_SENT_EMAIL_TO_USER => ['onEmailToUser', 0]];
    }

    public function onEmailToUser(TriggerExecutedEvent $event): void
    {
        $triggerEvent = $event->getTriggerEvent();
        $config       = $triggerEvent->getProperties();
        $lead         = $event->getLead();

        try {
            $this->sendEmailToUser->sendEmailToUsers($config, $lead);
            $event->setSucceded();
        } catch (EmailCouldNotBeSentException) {
            $event->setFailed();
        }
    }
}
