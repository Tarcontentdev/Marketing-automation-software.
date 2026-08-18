<?php

namespace MailVotech\NotificationBundle\EventListener;

use MailVotech\ChannelBundle\ChannelEvents;
use MailVotech\ChannelBundle\Event\ChannelEvent;
use MailVotech\ChannelBundle\Model\MessageModel;
use MailVotech\NotificationBundle\Entity\Notification;
use MailVotech\NotificationBundle\Form\Type\NotificationListType;
use MailVotech\PluginBundle\Helper\IntegrationHelper;
use MailVotech\ReportBundle\Model\ReportModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class ChannelSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private IntegrationHelper $integrationHelper,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ChannelEvents::ADD_CHANNEL => ['onAddChannel', 70],
        ];
    }

    public function onAddChannel(ChannelEvent $event): void
    {
        $integration = $this->integrationHelper->getIntegrationObject('OneSignal');

        if ($integration && $integration->getIntegrationSettings()->getIsPublished()) {
            $event->addChannel(
                'notification',
                [
                    MessageModel::CHANNEL_FEATURE => [
                        'campaignAction'             => CampaignSubscriber::EVENT_ACTION_SEND_NOTIFICATION,
                        'campaignDecisionsSupported' => [
                            'page.pagehit',
                            'asset.download',
                            'form.submit',
                        ],
                        'lookupFormType' => NotificationListType::class,
                        'repository'     => Notification::class,
                        'lookupOptions'  => [
                            'mobile'  => false,
                            'desktop' => true,
                        ],
                    ],
                    ReportModel::CHANNEL_FEATURE => [
                        'table' => 'push_notifications',
                    ],
                ]
            );

            $supportedFeatures = $integration->getSupportedFeatures();

            if (in_array('mobile', $supportedFeatures)) {
                $event->addChannel(
                    'mobile_notification',
                    [
                        MessageModel::CHANNEL_FEATURE => [
                            'campaignAction'             => CampaignSubscriber::EVENT_ACTION_SEND_MOBILE_NOTIFICATION,
                            'campaignDecisionsSupported' => [
                                'page.pagehit',
                                'asset.download',
                                'form.submit',
                            ],
                            'lookupFormType'             => NotificationListType::class,
                            'repository'                 => Notification::class,
                            'lookupOptions'              => [
                                'mobile'  => true,
                                'desktop' => false,
                            ],
                        ],
                    ]
                );
            }
        }
    }
}
