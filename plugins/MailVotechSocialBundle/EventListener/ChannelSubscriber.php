<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechSocialBundle\EventListener;

use MailVotech\ChannelBundle\ChannelEvents;
use MailVotech\ChannelBundle\Event\ChannelEvent;
use MailVotech\ChannelBundle\Model\MessageModel;
use MailVotech\PluginBundle\Helper\IntegrationHelper;
use MailVotechPlugin\MailVotechSocialBundle\Form\Type\TweetListType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class ChannelSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private IntegrationHelper $helper,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ChannelEvents::ADD_CHANNEL => ['onAddChannel', 80],
        ];
    }

    public function onAddChannel(ChannelEvent $event): void
    {
        $integration = $this->helper->getIntegrationObject('Twitter');
        if ($integration && $integration->getIntegrationSettings()->isPublished()) {
            $event->addChannel(
                'tweet',
                [
                    MessageModel::CHANNEL_FEATURE => [
                        'campaignAction'             => 'twitter.tweet',
                        'campaignDecisionsSupported' => [
                            'page.pagehit',
                            'asset.download',
                            'form.submit',
                        ],
                        'lookupFormType' => TweetListType::class,
                        'repository'     => 'MailVotechSocialBundle:Tweet',
                    ],
                ]
            );
        }
    }
}
