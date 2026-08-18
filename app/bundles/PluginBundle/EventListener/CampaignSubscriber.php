<?php

namespace MailVotech\PluginBundle\EventListener;

use MailVotech\CampaignBundle\CampaignEvents;
use MailVotech\CampaignBundle\Event\CampaignBuilderEvent;
use MailVotech\CampaignBundle\Event\CampaignExecutionEvent;
use MailVotech\PluginBundle\Form\Type\IntegrationsListType;
use MailVotech\PluginBundle\PluginEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class CampaignSubscriber implements EventSubscriberInterface
{
    use PushToIntegrationTrait;

    public static function getSubscribedEvents(): array
    {
        return [
            CampaignEvents::CAMPAIGN_ON_BUILD        => ['onCampaignBuild', 0],
            PluginEvents::ON_CAMPAIGN_TRIGGER_ACTION => ['onCampaignTriggerAction', 0],
        ];
    }

    public function onCampaignBuild(CampaignBuilderEvent $event): void
    {
        $action = [
            'label'       => 'mailvotech.plugin.actions.push_lead',
            'description' => 'mailvotech.plugin.actions.tooltip',
            'formType'    => IntegrationsListType::class,
            'formTheme'   => '@MailVotechPlugin/FormTheme/Integration/layout.html.twig',
            'eventName'   => PluginEvents::ON_CAMPAIGN_TRIGGER_ACTION,
        ];

        $event->addAction('plugin.leadpush', $action);
    }

    public function onCampaignTriggerAction(CampaignExecutionEvent $event): void
    {
        $config                  = $event->getConfig();
        $config['campaignEvent'] = $event->getEvent();
        $config['leadEventLog']  = $event->getLogEntry();
        $lead                    = $event->getLead();
        $errors                  = [];
        $success                 = $this->pushToIntegration($config, $lead, $errors);

        if (count($errors)) {
            $log = $event->getLogEntry();
            $log->appendToMetadata(
                [
                    'failed' => 1,
                    'reason' => implode('<br />', $errors),
                ]
            );
        }

        $event->setResult($success);
    }
}
