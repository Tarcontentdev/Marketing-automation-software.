<?php

namespace MailVotech\LeadBundle\EventListener;

use MailVotech\CampaignBundle\CampaignEvents;
use MailVotech\CampaignBundle\Entity\Campaign;
use MailVotech\CampaignBundle\Event\CampaignLeadChangeEvent;
use MailVotech\CoreBundle\Helper\UserHelper;
use MailVotech\CoreBundle\Translation\Translator;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Entity\LeadEventLog;
use MailVotech\LeadBundle\Entity\LeadEventLogRepository;
use MailVotech\LeadBundle\Event\LeadTimelineEvent;
use MailVotech\LeadBundle\LeadEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class TimelineEventLogCampaignSubscriber implements EventSubscriberInterface
{
    use TimelineEventLogTrait;

    public function __construct(
        LeadEventLogRepository $eventLogRepository,
        private UserHelper $userHelper,
        Translator $translator,
    ) {
        $this->eventLogRepository = $eventLogRepository;
        $this->translator         = $translator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CampaignEvents::CAMPAIGN_ON_LEADCHANGE     => 'onChange',
            CampaignEvents::LEAD_CAMPAIGN_BATCH_CHANGE => 'onBatchChange',
            LeadEvents::TIMELINE_ON_GENERATE           => 'onTimelineGenerate',
        ];
    }

    public function onChange(CampaignLeadChangeEvent $event): void
    {
        if (!$contact = $event->getLead()) {
            return;
        }

        $this->writeEntries(
            [$contact],
            $event->getCampaign(),
            $event->getAction()
        );
    }

    public function onBatchChange(CampaignLeadChangeEvent $event): void
    {
        if (!$contacts = $event->getLeads()) {
            return;
        }

        $this->writeEntries(
            $contacts,
            $event->getCampaign(),
            $event->getAction()
        );
    }

    public function onTimelineGenerate(LeadTimelineEvent $event): void
    {
        $this->addEvents(
            $event,
            'campaign_membership',
            'mailvotech.lead.timeline.campaign_membership',
            'ri-time-line',
            'campaign',
            'campaign'
        );
    }

    /**
     * @param Lead[] $contacts
     */
    private function writeEntries(array $contacts, Campaign $campaign, ?string $action): void
    {
        $user = $this->userHelper->getUser();

        $logs = [];
        foreach ($contacts as $contact) {
            $log = new LeadEventLog();
            $log->setUserId($user->getId())
                ->setUserName($user->getUserIdentifier() ?: $this->translator->trans('mailvotech.core.system'))
                ->setLead($contact)
                ->setBundle('campaign')
                ->setAction($action)
                ->setObject('campaign')
                ->setObjectId($campaign->getId())
                ->setProperties(
                    [
                        'campaign_id'        => $campaign->getId(),
                        'campaign_name'      => $campaign->getName(),
                        'object_description' => $campaign->getName(),
                    ]
                );

            $logs[] = $log;
        }

        $this->eventLogRepository->saveEntities($logs);
        $this->eventLogRepository->detachEntities($logs);
    }
}
