<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\Tests\Helper\EventListener;

use MailVotech\EmailBundle\EmailEvents;
use MailVotech\EmailBundle\Event\EmailValidationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class EmailValidationSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            EmailEvents::ON_EMAIL_VALIDATION => ['onEmailValidation', 0],
        ];
    }

    public function onEmailValidation(EmailValidationEvent $event): void
    {
        if ('bad@gmail.com' === $event->getAddress()) {
            $event->setInvalid('bad email');
        } // defaults to valid
    }
}
