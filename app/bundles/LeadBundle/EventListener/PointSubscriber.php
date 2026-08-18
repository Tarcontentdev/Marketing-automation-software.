<?php

namespace MailVotech\LeadBundle\EventListener;

use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Form\Type\ListActionType;
use MailVotech\LeadBundle\Form\Type\ModifyLeadTagsType;
use MailVotech\LeadBundle\Form\Type\StageType;
use MailVotech\LeadBundle\Model\LeadModel;
use MailVotech\PointBundle\Event\TriggerBuilderEvent;
use MailVotech\PointBundle\Event\TriggerExecutedEvent;
use MailVotech\PointBundle\PointEvents;
use MailVotech\StageBundle\Model\StageModel;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class PointSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LeadModel $leadModel,
        private StageModel $stageModel,
        private TranslatorInterface $translator,
        private LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PointEvents::TRIGGER_ON_BUILD                => ['onTriggerBuild', 0],
            PointEvents::TRIGGER_ON_EVENT_EXECUTE        => ['onTriggerExecute', 0],
            PointEvents::TRIGGER_ON_LEAD_SEGMENTS_CHANGE => ['onLeadSegmentsChange', 0],
        ];
    }

    public function onTriggerBuild(TriggerBuilderEvent $event): void
    {
        $event->addEvent(
            'lead.changelists',
            [
                'group'       => 'mailvotech.lead.point.trigger',
                'label'       => 'mailvotech.lead.point.trigger.changelists',
                'eventName'   => PointEvents::TRIGGER_ON_LEAD_SEGMENTS_CHANGE,
                'formType'    => ListActionType::class,
            ]
        );

        $event->addEvent(
            'lead.changetags',
            [
                'group'     => 'mailvotech.lead.point.trigger',
                'label'     => 'mailvotech.lead.lead.events.changetags',
                'formType'  => ModifyLeadTagsType::class,
                'eventName' => PointEvents::TRIGGER_ON_EVENT_EXECUTE,
            ]
        );

        $choices                 = [];
        $stages                  = $this->stageModel->getUserStages();
        $stageListItem           = $this->translator->trans('mailvotech.lead.stage.remove');
        $choices[$stageListItem] = 0;
        foreach ($stages as $stage) {
            $choices[$stage['name']] = $stage['id'];
        }

        $event->addEvent(
            'lead.changestage',
            [
                'group'           => 'mailvotech.lead.point.trigger',
                'label'           => 'mailvotech.lead.lead.events.changestage',
                'formType'        => StageType::class,
                'formTypeOptions' => ['items' => $choices],
                'eventName'       => PointEvents::TRIGGER_ON_EVENT_EXECUTE,
            ]
        );
    }

    public function onTriggerExecute(TriggerExecutedEvent $event): void
    {
        if ('lead.changetags' === $event->getTriggerEvent()->getType()) {
            $this->handleChangeTags($event);
        } elseif ('lead.changestage' === $event->getTriggerEvent()->getType()) {
            $this->handleChangeStage($event);
        }
    }

    private function handleChangeTags(TriggerExecutedEvent $event): void
    {
        $properties = $event->getTriggerEvent()->getProperties();
        $addTags    = $properties['add_tags'] ?: [];
        $removeTags = $properties['remove_tags'] ?: [];

        if ($this->leadModel->modifyTags($event->getLead(), $addTags, $removeTags)) {
            $event->setSucceded();
        }
    }

    private function handleChangeStage(TriggerExecutedEvent $event): void
    {
        $properties = $event->getTriggerEvent()->getProperties();
        $stageId    = (int) $properties['addstage'];
        $lead       = $event->getLead();

        if (0 === $stageId) {
            $this->handleRemoveStage($lead);
            $event->setSucceded();

            return;
        }

        try {
            $stage = $this->stageModel->getEntity($stageId);
            if (null === $stage || false === $stage->isPublished()) {
                throw new \InvalidArgumentException("Stage for ID $stageId not found");
            }

            $this->leadModel->changeStage(
                $lead,
                $stage,
                $this->translator->trans('mailvotech.lead.point.trigger')
            );

            $event->setSucceded();
        } catch (\UnexpectedValueException|\InvalidArgumentException $exception) {
            $event->setFailed();
            $this->logger->info("LeadBundle: Stage not updated for lead {$event->getLead()->getId()} by trigger because: {$exception->getMessage()}");
        }
    }

    private function handleRemoveStage(Lead $lead): void
    {
        $stage = $lead->getStage();

        if (null !== $stage) {
            $this->leadModel->removeFromStage(
                $lead,
                $stage,
                $this->translator->trans('mailvotech.stage.event.removed.batch')
            );
        }
    }

    public function onLeadSegmentsChange(TriggerExecutedEvent $event): void
    {
        $lead = $event->getLead();

        $properties = $event->getTriggerEvent()->getProperties();
        $addTo      = $properties['addToLists'];
        $removeFrom = $properties['removeFromLists'];

        if (!empty($addTo)) {
            $this->leadModel->addToLists($lead, $addTo);
        }

        if (!empty($removeFrom)) {
            $this->leadModel->removeFromLists($lead, $removeFrom);
        }
    }
}
