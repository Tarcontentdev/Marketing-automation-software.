<?php

declare(strict_types=1);

namespace MailVotech\AssetBundle\EventListener;

use MailVotech\AssetBundle\AssetEvents;
use MailVotech\EmailBundle\EmailEvents;
use MailVotech\EmailBundle\Event\EmailBuilderEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class EmailSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            EmailEvents::EMAIL_ON_BUILD => ['onEmailBuild', 0],
        ];
    }

    public function onEmailBuild(EmailBuilderEvent $event): void
    {
        if ($event->abTestWinnerCriteriaRequested()) {
            // add AB Test Winner Criteria
            $formSubmissions = [
                'group'    => 'mailvotech.asset.abtest.criteria',
                'label'    => 'mailvotech.asset.abtest.criteria.downloads',
                'event'    => AssetEvents::ON_DETERMINE_DOWNLOAD_RATE_WINNER,
            ];
            $event->addAbTestWinnerCriteria('asset.downloads', $formSubmissions);
        }
    }
}
