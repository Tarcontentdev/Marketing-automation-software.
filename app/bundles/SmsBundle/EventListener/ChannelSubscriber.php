<?php

declare(strict_types=1);

namespace MailVotech\SmsBundle\EventListener;

use MailVotech\ChannelBundle\ChannelEvents;
use MailVotech\ChannelBundle\Event\ChannelEvent;
use MailVotech\ChannelBundle\Model\MessageModel;
use MailVotech\LeadBundle\Model\LeadModel;
use MailVotech\ReportBundle\Model\ReportModel;
use MailVotech\SmsBundle\Entity\Sms;
use MailVotech\SmsBundle\Form\Type\SmsListType;
use MailVotech\SmsBundle\Sms\TransportChain;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class ChannelSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private TransportChain $transportChain,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ChannelEvents::ADD_CHANNEL => ['onAddChannel', 90],
        ];
    }

    public function onAddChannel(ChannelEvent $event): void
    {
        if (count($this->transportChain->getEnabledTransports()) > 0) {
            $event->addChannel(
                'sms',
                [
                    MessageModel::CHANNEL_FEATURE => [
                        'campaignAction'             => 'sms.send_text_sms',
                        'campaignDecisionsSupported' => [
                            'page.pagehit',
                            'asset.download',
                            'form.submit',
                        ],
                        'lookupFormType' => SmsListType::class,
                        'repository'     => Sms::class,
                    ],
                    LeadModel::CHANNEL_FEATURE   => [],
                    ReportModel::CHANNEL_FEATURE => [
                        'table' => 'sms_messages',
                    ],
                ]
            );
        }
    }
}
