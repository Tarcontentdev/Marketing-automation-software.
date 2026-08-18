<?php

declare(strict_types=1);

namespace MailVotech\FormBundle\EventListener;

use MailVotech\EmailBundle\EmailEvents;
use MailVotech\EmailBundle\Event\EmailBuilderEvent;
use MailVotech\FormBundle\FormEvents;
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
                'group'    => 'mailvotech.form.abtest.criteria',
                'label'    => 'mailvotech.form.abtest.criteria.submissions',
                'event'    => FormEvents::ON_DETERMINE_SUBMISSION_RATE_WINNER,
            ];
            $event->addAbTestWinnerCriteria('form.submissions', $formSubmissions);
        }
    }
}
