<?php

namespace MailVotechPlugin\MailVotechSocialBundle\EventListener;

use MailVotech\CampaignBundle\CampaignEvents;
use MailVotech\CampaignBundle\Event\CampaignBuilderEvent;
use MailVotech\CampaignBundle\Event\CampaignExecutionEvent;
use MailVotech\PluginBundle\Helper\IntegrationHelper;
use MailVotechPlugin\MailVotechSocialBundle\Form\Type\TweetSendType;
use MailVotechPlugin\MailVotechSocialBundle\Helper\CampaignEventHelper;
use MailVotechPlugin\MailVotechSocialBundle\SocialEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class CampaignSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private CampaignEventHelper $campaignEventHelper,
        private IntegrationHelper $integrationHelper,
        private TranslatorInterface $translator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CampaignEvents::CAMPAIGN_ON_BUILD        => ['onCampaignBuild', 0],
            SocialEvents::ON_CAMPAIGN_TRIGGER_ACTION => ['onCampaignAction', 0],
        ];
    }

    public function onCampaignBuild(CampaignBuilderEvent $event): void
    {
        $integration = $this->integrationHelper->getIntegrationObject('Twitter');
        if ($integration && $integration->getIntegrationSettings()->isPublished()) {
            $action = [
                'label'           => 'mailvotech.social.twitter.tweet.event.open',
                'description'     => 'mailvotech.social.twitter.tweet.event.open_desc',
                'eventName'       => SocialEvents::ON_CAMPAIGN_TRIGGER_ACTION,
                'formTypeOptions' => ['update_select' => 'campaignevent_properties_channelId'],
                'formType'        => TweetSendType::class,
                'channel'         => 'social.tweet',
                'channelIdField'  => 'channelId',
            ];

            $event->addAction('twitter.tweet', $action);
        }
    }

    public function onCampaignAction(CampaignExecutionEvent $event): void
    {
        $event->setChannel('social.twitter');
        if ($response = $this->campaignEventHelper->sendTweetAction($event->getLead(), $event->getEvent())) {
            $event->setResult($response);

            return;
        }

        $event->setFailed(
            $this->translator->trans('mailvotech.social.twitter.error.handle_not_found')
        );
    }
}
