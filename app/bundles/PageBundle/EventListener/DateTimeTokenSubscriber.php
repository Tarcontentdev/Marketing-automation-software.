<?php

namespace MailVotech\PageBundle\EventListener;

use MailVotech\CoreBundle\Helper\DateTime\DateTimeToken;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\LeadBundle\Tracker\ContactTracker;
use MailVotech\PageBundle\Event\PageBuilderEvent;
use MailVotech\PageBundle\Event\PageDisplayEvent;
use MailVotech\PageBundle\PageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class DateTimeTokenSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private TranslatorInterface $translator,
        private DateTimeToken $dateTokenHelper,
        private CorePermissions $security,
        private ContactTracker $contactTracker,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PageEvents::PAGE_ON_BUILD                     => ['onPageBuild', 0],
            PageEvents::PAGE_ON_DISPLAY                   => ['onPageDisplay', 0],
        ];
    }

    public function onPageBuild(PageBuilderEvent $event): void
    {
        $event->addToken('{today}', $this->translator->trans('mailvotech.core.token.group.other').': '.$this->translator->trans('mailvotech.email.token.today'));
    }

    public function onPageDisplay(PageDisplayEvent $event): void
    {
        $content   = $event->getContent();
        $contact   = $this->security->isAnonymous() ? $this->contactTracker->getContact() : null;

        $tokenList = $this->dateTokenHelper->getTokens($content, $contact ? $contact->getTimezone() : null);
        $event->setContent(str_replace(array_keys($tokenList), $tokenList, $content));
    }
}
