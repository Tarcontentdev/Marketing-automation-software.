<?php

namespace MailVotech\StageBundle\EventListener;

use MailVotech\CampaignBundle\CampaignEvents;
use MailVotech\CampaignBundle\Entity\LeadEventLog;
use MailVotech\CampaignBundle\Event\CampaignBuilderEvent;
use MailVotech\CampaignBundle\Event\PendingEvent;
use MailVotech\LeadBundle\Model\LeadModel;
use MailVotech\StageBundle\Entity\Stage;
use MailVotech\StageBundle\Form\Type\StageActionChangeType;
use MailVotech\StageBundle\Model\StageModel;
use MailVotech\StageBundle\StageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class CampaignSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LeadModel $leadModel,
        private StageModel $stageModel,
        private TranslatorInterface $translator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CampaignEvents::CAMPAIGN_ON_BUILD     => ['onCampaignBuild', 0],
            StageEvents::ON_CAMPAIGN_BATCH_ACTION => ['onCampaignTriggerStageChange', 0],
        ];
    }

    public function onCampaignBuild(CampaignBuilderEvent $event): void
    {
        $action = [
            'label'            => 'mailvotech.stage.campaign.event.change',
            'description'      => 'mailvotech.stage.campaign.event.change_descr',
            'batchEventName'   => StageEvents::ON_CAMPAIGN_BATCH_ACTION,
            'formType'         => StageActionChangeType::class,
            'formTheme'        => '@MailVotechStage/FormTheme/Action/_stageaction_properties_row.html.twig',
        ];
        $event->addAction('stage.change', $action);
    }

    public function onCampaignTriggerStageChange(PendingEvent $event): void
    {
        $logs    = $event->getPending();
        $config  = $event->getEvent()->getProperties();
        $stageId = (int) $config['stage'];
        $stage   = $this->stageModel->getEntity($stageId);

        if (!$stage || !$stage->isPublished()) {
            $event->passAllWithError($this->translator->trans('mailvotech.stage.campaign.event.stage_missing'));

            return;
        }

        foreach ($logs as $log) {
            $this->changeStage($log, $stage, $event);
        }
    }

    private function changeStage(LeadEventLog $log, Stage $stage, PendingEvent $pendingEvent): void
    {
        try {
            $this->leadModel->changeStage($log->getLead(), $stage, $log->getEvent()->getName());
            $pendingEvent->pass($log);
        } catch (\UnexpectedValueException $exception) {
            $pendingEvent->passWithError($log, $exception->getMessage());
        }
    }
}
