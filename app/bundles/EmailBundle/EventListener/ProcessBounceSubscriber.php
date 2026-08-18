<?php

namespace MailVotech\EmailBundle\EventListener;

use MailVotech\EmailBundle\EmailEvents;
use MailVotech\EmailBundle\Event\MonitoredEmailEvent;
use MailVotech\EmailBundle\Event\ParseEmailEvent;
use MailVotech\EmailBundle\MonitoredEmail\Processor\Bounce;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class ProcessBounceSubscriber implements EventSubscriberInterface
{
    public const BUNDLE     = 'EmailBundle';

    public const FOLDER_KEY = 'bounces';

    public static function getSubscribedEvents(): array
    {
        return [
            EmailEvents::MONITORED_EMAIL_CONFIG => ['onEmailConfig', 0],
            EmailEvents::EMAIL_PARSE            => ['onEmailParse', 0],
        ];
    }

    public function __construct(
        private Bounce $bouncer,
    ) {
    }

    public function onEmailConfig(MonitoredEmailEvent $event): void
    {
        $event->addFolder(self::BUNDLE, self::FOLDER_KEY, 'mailvotech.email.config.monitored_email.bounce_folder');
    }

    public function onEmailParse(ParseEmailEvent $event): void
    {
        if ($event->isApplicable(self::BUNDLE, self::FOLDER_KEY)) {
            // Process the messages
            $messages = $event->getMessages();
            foreach ($messages as $message) {
                $this->bouncer->process($message);
            }
        }
    }
}
