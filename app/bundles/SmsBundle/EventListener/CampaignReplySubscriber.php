<?php

namespace MailVotech\SmsBundle\EventListener;

use MailVotech\CampaignBundle\CampaignEvents;
use MailVotech\CampaignBundle\Event\CampaignBuilderEvent;
use MailVotech\CampaignBundle\Event\DecisionEvent;
use MailVotech\CampaignBundle\Executioner\RealTimeExecutioner;
use MailVotech\SmsBundle\Event\ReplyEvent;
use MailVotech\SmsBundle\Form\Type\CampaignReplyType;
use MailVotech\SmsBundle\Helper\ReplyHelper;
use MailVotech\SmsBundle\Sms\TransportChain;
use MailVotech\SmsBundle\SmsEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class CampaignReplySubscriber implements EventSubscriberInterface
{
    public const TYPE = 'sms.reply';

    public function __construct(
        private TransportChain $transportChain,
        private RealTimeExecutioner $realTimeExecutioner,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CampaignEvents::CAMPAIGN_ON_BUILD => ['onCampaignBuild', 0],
            SmsEvents::ON_CAMPAIGN_REPLY      => ['onCampaignReply', 0],
            SmsEvents::ON_REPLY               => ['onReply', 0],
        ];
    }

    public function onCampaignBuild(CampaignBuilderEvent $event): void
    {
        if (0 === count($this->transportChain->getEnabledTransports())) {
            return;
        }

        $event->addDecision(
            self::TYPE,
            [
                'label'       => 'mailvotech.campaign.sms.reply',
                'description' => 'mailvotech.campaign.sms.reply.tooltip',
                'eventName'   => SmsEvents::ON_CAMPAIGN_REPLY,
                'formType'    => CampaignReplyType::class,
            ]
        );
    }

    public function onCampaignReply(DecisionEvent $decisionEvent): void
    {
        /** @var ReplyEvent $replyEvent */
        $replyEvent = $decisionEvent->getPassthrough();
        $pattern    = $decisionEvent->getLog()->getEvent()->getProperties()['pattern'];

        if (empty($pattern)) {
            // Assume any reply
            $decisionEvent->setAsApplicable();

            return;
        }

        if (!ReplyHelper::matches($pattern, $replyEvent->getMessage())) {
            // It does not match so ignore

            return;
        }

        $decisionEvent->setChannel('sms');
        $decisionEvent->setAsApplicable();
    }

    /**
     * @throws \MailVotech\CampaignBundle\Executioner\Dispatcher\Exception\LogNotProcessedException
     * @throws \MailVotech\CampaignBundle\Executioner\Dispatcher\Exception\LogPassedAndFailedException
     * @throws \MailVotech\CampaignBundle\Executioner\Exception\CannotProcessEventException
     * @throws \MailVotech\CampaignBundle\Executioner\Scheduler\Exception\NotSchedulableException
     */
    public function onReply(ReplyEvent $event): void
    {
        $this->realTimeExecutioner->execute(self::TYPE, $event, 'sms');
    }
}
