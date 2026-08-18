<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\EventListener;

use MailVotech\EmailBundle\EmailEvents;
use MailVotech\EmailBundle\Event\EmailEvent;
use MailVotech\EmailBundle\Helper\EmailDefaultsHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class EmailDefaultsSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EmailDefaultsHelper $defaultsHelper,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EmailEvents::EMAIL_PRE_SAVE => ['onEmailPreSave', 0],
        ];
    }

    public function onEmailPreSave(EmailEvent $event): void
    {
        $email = $event->getEmail();

        if (!$event->isNew() || $email->getIsClone()) {
            return;
        }

        $this->defaultsHelper->applyDefaults($email);
    }
}
