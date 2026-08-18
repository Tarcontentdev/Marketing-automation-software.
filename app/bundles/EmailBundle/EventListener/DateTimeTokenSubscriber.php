<?php

namespace MailVotech\EmailBundle\EventListener;

use MailVotech\CoreBundle\Helper\DateTime\DateTimeToken;
use MailVotech\EmailBundle\EmailEvents;
use MailVotech\EmailBundle\Event\EmailBuilderEvent;
use MailVotech\EmailBundle\Event\EmailSendEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class DateTimeTokenSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private TranslatorInterface $translator,
        private DateTimeToken $dateTokenHelper,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EmailEvents::EMAIL_ON_BUILD                     => ['onEmailBuild', 0],
            EmailEvents::EMAIL_ON_SEND                      => ['onEmailGenerate', 0],
            EmailEvents::EMAIL_ON_DISPLAY                   => ['onEmailDisplay', 0],
        ];
    }

    public function onEmailBuild(EmailBuilderEvent $event): void
    {
        $event->addToken('{today}', $this->translator->trans('mailvotech.core.token.group.other').': '.$this->translator->trans('mailvotech.email.token.today'));
    }

    public function onEmailDisplay(EmailSendEvent $event): void
    {
        $this->onEmailGenerate($event);
    }

    public function onEmailGenerate(EmailSendEvent $event): void
    {
        $content = $event->getSubject();
        $content .= $event->getContent();
        $content .= $event->getPlainText();
        $content .= implode(' ', $event->getTextHeaders());

        $leadArray       = $event->getLead();
        $contactTimezone = $event->isInternalSend() || !is_array($leadArray) ? null : ($leadArray['timezone'] ?? null);
        $tokenList       = $this->dateTokenHelper->getTokens($content, $contactTimezone);
        if (count($tokenList)) {
            $event->addTokens($tokenList);
            unset($tokenList);
        }
    }
}
