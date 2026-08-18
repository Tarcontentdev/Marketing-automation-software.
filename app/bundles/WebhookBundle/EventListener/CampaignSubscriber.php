<?php

namespace MailVotech\WebhookBundle\EventListener;

use MailVotech\CampaignBundle\CampaignEvents;
use MailVotech\CampaignBundle\Event as Events;
use MailVotech\CampaignBundle\Event\CampaignExecutionEvent;
use MailVotech\WebhookBundle\Form\Type\CampaignEventSendWebhookType;
use MailVotech\WebhookBundle\Helper\CampaignHelper;
use MailVotech\WebhookBundle\WebhookEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class CampaignSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private CampaignHelper $campaignHelper,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CampaignEvents::CAMPAIGN_ON_BUILD         => ['onCampaignBuild', 0],
            WebhookEvents::ON_CAMPAIGN_TRIGGER_ACTION => ['onCampaignTriggerAction', 0],
        ];
    }

    public function onCampaignTriggerAction(CampaignExecutionEvent $event): void
    {
        if ($event->checkContext('campaign.sendwebhook')) {
            try {
                $this->campaignHelper->fireWebhook($event->getConfig(), $event->getLead());
                $event->setResult(true);
            } catch (\Exception $e) {
                $event->setFailed($e->getMessage());
            }
        }
    }

    /**
     * Add event triggers and actions.
     */
    public function onCampaignBuild(Events\CampaignBuilderEvent $event): void
    {
        $sendWebhookAction = [
            'label'              => 'mailvotech.webhook.event.sendwebhook',
            'description'        => 'mailvotech.webhook.event.sendwebhook_desc',
            'formType'           => CampaignEventSendWebhookType::class,
            'formTypeCleanMasks' => 'clean',
            'eventName'          => WebhookEvents::ON_CAMPAIGN_TRIGGER_ACTION,
        ];
        $event->addAction('campaign.sendwebhook', $sendWebhookAction);
    }
}
