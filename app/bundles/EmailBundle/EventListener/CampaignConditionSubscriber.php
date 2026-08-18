<?php

namespace MailVotech\EmailBundle\EventListener;

use MailVotech\CampaignBundle\CampaignEvents;
use MailVotech\CampaignBundle\Event\CampaignBuilderEvent;
use MailVotech\CampaignBundle\Event\CampaignExecutionEvent;
use MailVotech\EmailBundle\EmailEvents;
use MailVotech\EmailBundle\Exception\InvalidEmailException;
use MailVotech\EmailBundle\Helper\EmailValidator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

final readonly class CampaignConditionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EmailValidator $validator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CampaignEvents::CAMPAIGN_ON_BUILD          => ['onCampaignBuild', 0],
            EmailEvents::ON_CAMPAIGN_TRIGGER_CONDITION => ['onCampaignTriggerCondition', 0],
        ];
    }

    public function onCampaignBuild(CampaignBuilderEvent $event): void
    {
        $event->addCondition(
            'email.validate.address',
            [
                'label'       => 'mailvotech.email.campaign.event.validate_address',
                'description' => 'mailvotech.email.campaign.event.validate_address_descr',
                'eventName'   => EmailEvents::ON_CAMPAIGN_TRIGGER_CONDITION,
            ]
        );
    }

    public function onCampaignTriggerCondition(CampaignExecutionEvent $event): void
    {
        try {
            $this->validator->validate($event->getLead()->getEmail(), true);
        } catch (UnexpectedValueException|InvalidEmailException) {
            $event->setResult(false);

            return;
        }

        $event->setResult(true);
    }
}
