<?php

namespace MailVotech\SmsBundle\EventListener;

use MailVotech\CampaignBundle\CampaignEvents;
use MailVotech\CampaignBundle\Event\CampaignBuilderEvent;
use MailVotech\CampaignBundle\Event\PendingEvent;
use MailVotech\SmsBundle\Entity\Sms;
use MailVotech\SmsBundle\Form\Type\SmsSendType;
use MailVotech\SmsBundle\Model\SmsModel;
use MailVotech\SmsBundle\Sms\TransportChain;
use MailVotech\SmsBundle\SmsEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class CampaignSendSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private SmsModel $smsModel,
        private TransportChain $transportChain,
        private TranslatorInterface $translator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CampaignEvents::CAMPAIGN_ON_BUILD           => ['onCampaignBuild', 0],
            SmsEvents::ON_CAMPAIGN_TRIGGER_BATCH_ACTION => ['onCampaignTriggerBatchAction', 0],
        ];
    }

    public function onCampaignBuild(CampaignBuilderEvent $event): void
    {
        if (count($this->transportChain->getEnabledTransports()) > 0) {
            $event->addAction(
                'sms.send_text_sms',
                [
                    'label'            => 'mailvotech.campaign.sms.send_text_sms',
                    'description'      => 'mailvotech.campaign.sms.send_text_sms.tooltip',
                    'batchEventName'   => SmsEvents::ON_CAMPAIGN_TRIGGER_BATCH_ACTION,
                    'formType'         => SmsSendType::class,
                    'formTypeOptions'  => ['update_select' => 'campaignevent_properties_sms'],
                    'formTheme'        => '@MailVotechSms/FormTheme/SmsSendList/smssend_list_row.html.twig',
                    'channel'          => 'sms',
                    'channelIdField'   => 'sms',
                ]
            );
        }
    }

    public function onCampaignTriggerBatchAction(PendingEvent $event): void
    {
        $smsId = (int) $event->getEvent()->getProperties()['sms'];
        $sms   = $smsId ? $this->smsModel->getEntity($smsId) : null;

        if (!$sms) {
            $event->passAllWithError($this->translator->trans('mailvotech.sms.campaign.failed.missing_entity'));

            return;
        }

        if (!$sms->isPublished()) {
            $event->passAllWithError($this->translator->trans('mailvotech.sms.campaign.failed.unpublished'));

            return;
        }

        $event->setChannel('sms', $sms->getId());
        $this->sendSmsInBatches($sms, $event);
    }

    private function sendSmsInBatches(Sms $sms, PendingEvent $event): void
    {
        $contacts = $event->getContacts()->toArray();
        $result   = $this->smsModel->sendSMS($sms, $contacts, ['channel' => ['campaign.event', $event->getEvent()->getId()]]);
        $this->processResponse($event, $result);
    }

    /**
     * @param mixed[] $result
     */
    private function processResponse(PendingEvent $event, array $result): void
    {
        foreach ($event->getPending() as $log) {
            if (isset($result[$log->getLead()->getId()])) {
                $log->appendToMetadata($result);
                $event->pass($log);
            }
        }

        $event->failRemaining($this->translator->trans('mailvotech.sms.campaign.failed.missing_entity'));
    }
}
