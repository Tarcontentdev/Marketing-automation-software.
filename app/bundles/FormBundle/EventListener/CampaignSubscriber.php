<?php

namespace MailVotech\FormBundle\EventListener;

use MailVotech\CampaignBundle\CampaignEvents;
use MailVotech\CampaignBundle\Event\CampaignBuilderEvent;
use MailVotech\CampaignBundle\Event\CampaignExecutionEvent;
use MailVotech\CampaignBundle\Executioner\RealTimeExecutioner;
use MailVotech\CoreBundle\Helper\InputHelper;
use MailVotech\FormBundle\Entity\Form;
use MailVotech\FormBundle\Entity\FormRepository;
use MailVotech\FormBundle\Entity\SubmissionRepository;
use MailVotech\FormBundle\Event\SubmissionEvent;
use MailVotech\FormBundle\Form\Type\CampaignEventFormFieldValueType;
use MailVotech\FormBundle\Form\Type\CampaignEventFormSubmitType;
use MailVotech\FormBundle\FormEvents;
use MailVotech\FormBundle\Helper\FormFieldHelper;
use MailVotech\FormBundle\Model\FormModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class CampaignSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private FormModel $formModel,
        private RealTimeExecutioner $realTimeExecutioner,
        private FormFieldHelper $formFieldHelper,
        private FormRepository $formRepository,
        private SubmissionRepository $submissionRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CampaignEvents::CAMPAIGN_ON_BUILD         => ['onCampaignBuild', 0],
            FormEvents::FORM_ON_SUBMIT                => ['onFormSubmit', 0],
            FormEvents::ON_CAMPAIGN_TRIGGER_DECISION  => ['onCampaignTriggerDecision', 0],
            FormEvents::ON_CAMPAIGN_TRIGGER_CONDITION => ['onCampaignTriggerCondition', 0],
        ];
    }

    /**
     * Add the option to the list.
     */
    public function onCampaignBuild(CampaignBuilderEvent $event): void
    {
        $trigger = [
            'label'       => 'mailvotech.form.campaign.event.submit',
            'description' => 'mailvotech.form.campaign.event.submit_descr',
            'formType'    => CampaignEventFormSubmitType::class,
            'eventName'   => FormEvents::ON_CAMPAIGN_TRIGGER_DECISION,
        ];
        $event->addDecision('form.submit', $trigger);

        $trigger = [
            'label'       => 'mailvotech.form.campaign.event.field_value',
            'description' => 'mailvotech.form.campaign.event.field_value_descr',
            'formType'    => CampaignEventFormFieldValueType::class,
            'formTheme'   => '@MailVotechForm/FormTheme/FieldValueCondition/_campaignevent_form_field_value_widget.html.twig',
            'eventName'   => FormEvents::ON_CAMPAIGN_TRIGGER_CONDITION,
        ];
        $event->addCondition('form.field_value', $trigger);
    }

    /**
     * Trigger campaign event for when a form is submitted.
     */
    public function onFormSubmit(SubmissionEvent $event): void
    {
        $form = $event->getSubmission()->getForm();
        $this->realTimeExecutioner->execute('form.submit', $form, 'form', $form->getId());
    }

    public function onCampaignTriggerDecision(CampaignExecutionEvent $event): void
    {
        $eventDetails = $event->getEventDetails();

        if (null === $eventDetails) {
            $event->setResult(true);

            return;
        }

        if (!$eventDetails instanceof Form) {
            $event->setResult(false);

            return;
        }

        $limitToForms = $event->getConfig()['forms'];

        // check against selected forms
        if (!empty($limitToForms) && !in_array($eventDetails->getId(), $limitToForms)) {
            $event->setResult(false);

            return;
        }

        $event->setResult(true);
    }

    public function onCampaignTriggerCondition(CampaignExecutionEvent $event): void
    {
        $lead = $event->getLead();

        if (!$lead || !$lead->getId()) {
            $event->setResult(false);

            return;
        }

        $operators = $this->formModel->getFilterExpressionFunctions();
        $form      = $this->formRepository->findOneById($event->getConfig()['form']);

        if (!$form || !$form->getId()) {
            $event->setResult(false);

            return;
        }

        $field = $this->formModel->findFormFieldByAlias($form, $event->getConfig()['field']);

        $filter = $this->formFieldHelper->getFieldFilter($field->getType());
        $value  = InputHelper::_($event->getConfig()['value'], $filter);

        $result = $this->submissionRepository->compareValue(
            $lead->getId(),
            $form->getId(),
            $form->getAlias(),
            $event->getConfig()['field'],
            $value,
            $operators[$event->getConfig()['operator']]['expr'],
            $field ? $field->getType() : null
        );

        $event->setChannel('form', $form->getId());

        $event->setResult($result);
    }
}
