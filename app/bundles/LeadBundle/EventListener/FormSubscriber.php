<?php

namespace MailVotech\LeadBundle\EventListener;

use MailVotech\CoreBundle\Helper\IpLookupHelper;
use MailVotech\FormBundle\Crate\FieldCrate;
use MailVotech\FormBundle\Crate\ObjectCrate;
use MailVotech\FormBundle\Event\FieldCollectEvent;
use MailVotech\FormBundle\Event\FormBuilderEvent;
use MailVotech\FormBundle\Event\ObjectCollectEvent;
use MailVotech\FormBundle\Event\SubmissionEvent;
use MailVotech\FormBundle\FormEvents;
use MailVotech\LeadBundle\Entity\LeadFieldRepository;
use MailVotech\LeadBundle\Entity\PointsChangeLog;
use MailVotech\LeadBundle\Entity\UtmTag;
use MailVotech\LeadBundle\Form\Type\ActionAddUtmTagsType;
use MailVotech\LeadBundle\Form\Type\ActionRemoveDoNotContact;
use MailVotech\LeadBundle\Form\Type\CompanyChangeScoreActionType;
use MailVotech\LeadBundle\Form\Type\FormSubmitActionPointsChangeType;
use MailVotech\LeadBundle\Form\Type\ListActionType;
use MailVotech\LeadBundle\Form\Type\ModifyLeadTagsType;
use MailVotech\LeadBundle\Form\Type\UpdateLeadActionType;
use MailVotech\LeadBundle\Helper\CustomFieldHelper;
use MailVotech\LeadBundle\Helper\TokenHelper;
use MailVotech\LeadBundle\LeadEvents;
use MailVotech\LeadBundle\Model\DoNotContact;
use MailVotech\LeadBundle\Model\FieldModel;
use MailVotech\LeadBundle\Model\LeadModel;
use MailVotech\LeadBundle\Tracker\ContactTracker;
use MailVotech\PointBundle\Model\PointGroupModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class FormSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LeadModel $leadModel,
        private ContactTracker $contactTracker,
        private IpLookupHelper $ipLookupHelper,
        private LeadFieldRepository $leadFieldRepository,
        private PointGroupModel $groupModel,
        private DoNotContact $doNotContact,
        private FieldModel $leadFieldModel,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::FORM_ON_BUILD                    => ['onFormBuilder', 0],
            FormEvents::ON_OBJECT_COLLECT                => ['onObjectCollect', 0],
            FormEvents::ON_FIELD_COLLECT                 => ['onFieldCollect', 0],
            LeadEvents::LEAD_ON_SEGMENTS_CHANGE          => ['onLeadSegmentsChange', 0],
            FormEvents::ON_EXECUTE_SUBMIT_ACTION         => [
                ['onFormSubmitActionChangePoints', 0],
                ['onFormSubmitActionChangeList', 1],
                ['onFormSubmitActionChangeTags', 2],
                ['onFormSubmitActionAddUtmTags', 3],
                ['onFormSubmitActionScoreContactsCompanies', 4],
                ['onFormSubmitActionRemoveFromDoNotContact', 5],
                ['onFormSubmitActionUpdateLead', 5],
            ],
        ];
    }

    /**
     * Add a lead generation action to available form submit actions.
     */
    public function onFormBuilder(FormBuilderEvent $event): void
    {
        $event->addSubmitAction('lead.pointschange', [
            'group'       => 'mailvotech.lead.lead.submitaction',
            'label'       => 'mailvotech.lead.lead.submitaction.changepoints',
            'description' => 'mailvotech.lead.lead.submitaction.changepoints_descr',
            'formType'    => FormSubmitActionPointsChangeType::class,
            'formTheme'   => '@MailVotechLead/FormTheme/FormActionChangePoints/_formaction_properties_row.html.twig',
            'eventName'   => FormEvents::ON_EXECUTE_SUBMIT_ACTION,
            'template'    => '@MailVotechLead/Action/points.html.twig',
        ]);

        $event->addSubmitAction('lead.changelist', [
            'group'       => 'mailvotech.lead.lead.submitaction',
            'label'       => 'mailvotech.lead.lead.events.changelist',
            'description' => 'mailvotech.lead.lead.events.changelist_descr',
            'formType'    => ListActionType::class,
            'eventName'   => FormEvents::ON_EXECUTE_SUBMIT_ACTION,
            'template'    => '@MailVotechLead/Action/segments.html.twig',
        ]);

        $event->addSubmitAction('lead.changetags', [
            'group'       => 'mailvotech.lead.lead.submitaction',
            'label'       => 'mailvotech.lead.lead.events.changetags',
            'description' => 'mailvotech.lead.lead.events.changetags_descr',
            'formType'    => ModifyLeadTagsType::class,
            'eventName'   => FormEvents::ON_EXECUTE_SUBMIT_ACTION,
            'template'    => '@MailVotechLead/Action/tags.html.twig',
        ]);

        $event->addSubmitAction('lead.addutmtags', [
            'group'       => 'mailvotech.lead.lead.submitaction',
            'label'       => 'mailvotech.lead.lead.events.addutmtags',
            'description' => 'mailvotech.lead.lead.events.addutmtags_descr',
            'formType'    => ActionAddUtmTagsType::class,
            'formTheme'   => '@MailVotechLead/FormTheme/FormActionAddUtmTags/_formaction_properties_row.html.twig',
            'eventName'   => FormEvents::ON_EXECUTE_SUBMIT_ACTION,
        ]);

        $event->addSubmitAction('lead.remove_do_not_contact', [
            'group'       => 'mailvotech.lead.lead.submitaction',
            'label'       => 'mailvotech.lead.lead.events.removedonotcontact',
            'description' => 'mailvotech.lead.lead.events.removedonotcontact_descr',
            'formType'    => ActionRemoveDoNotContact::class,
            'eventName'   => FormEvents::ON_EXECUTE_SUBMIT_ACTION,
        ]);

        $event->addSubmitAction('lead.scorecontactscompanies', [
            'group'       => 'mailvotech.lead.lead.submitaction',
            'label'       => 'mailvotech.lead.lead.events.changecompanyscore',
            'description' => 'mailvotech.lead.lead.events.changecompanyscore_descr',
            'formType'    => CompanyChangeScoreActionType::class,
            'eventName'   => FormEvents::ON_EXECUTE_SUBMIT_ACTION,
            'template'    => '@MailVotechLead/Action/points.html.twig',
        ]);

        $event->addSubmitAction('lead.updatelead', [
            'group'       => 'mailvotech.lead.lead.submitaction',
            'label'       => 'mailvotech.lead.lead.events.updatelead',
            'description' => 'mailvotech.lead.lead.events.updatelead_descr',
            'formType'    => UpdateLeadActionType::class,
            'formTheme'   => '@MailVotechLead/FormTheme/FormActionUpdateLead/_formaction_properties_row.html.twig',
            'eventName'   => FormEvents::ON_EXECUTE_SUBMIT_ACTION,
        ]);
    }

    public function onObjectCollect(ObjectCollectEvent $event): void
    {
        $event->appendObject(new ObjectCrate('contact', 'mailvotech.lead.contact'));
        $event->appendObject(new ObjectCrate('company', 'mailvotech.core.company'));
    }

    public function onFieldCollect(FieldCollectEvent $event): void
    {
        $object = 'contact' === $event->getObject() ? 'lead' : $event->getObject(); // BC conversion.
        $fields = $this->leadFieldRepository->getFieldsForObject($object);

        foreach ($fields as $field) {
            $event->appendField(
                new FieldCrate(
                    $field->getAlias(),
                    $field->getLabel(),
                    $field->getType(),
                    $field->getProperties()
                )
            );
        }

        // Add the owner and stage fields to the form
        if ('lead' === $object) {
            $event->appendField(new FieldCrate('ownerbyemail', 'mailvotech.lead.field.ownerbyemail', 'email', []));
            $event->appendField(new FieldCrate('ownerbyid', 'mailvotech.lead.field.ownerbyid', 'text', []));
            $event->appendField(new FieldCrate('stagebyname', 'mailvotech.lead.field.stagebyname', 'text', []));
        }
    }

    public function onFormSubmitActionChangePoints(SubmissionEvent $event): void
    {
        if (false === $event->checkContext('lead.pointschange')) {
            return;
        }

        if (!$contact = $this->contactTracker->getContact()) {
            return;
        }

        $form = $event->getSubmission()->getForm();

        $pointsChangeLog = new PointsChangeLog();
        $pointsChangeLog->setType('form');
        $pointsChangeLog->setEventName($form->getId().':'.$form->getName());
        $pointsChangeLog->setActionName($event->getAction()->getName());
        $pointsChangeLog->setIpAddress($this->ipLookupHelper->getIpAddress());
        $pointsChangeLog->setDateAdded(new \DateTime());
        $pointsChangeLog->setLead($contact);

        $oldPoints  = $contact->getPoints();
        $properties = $event->getActionConfig();

        $operator     = $properties['operator'];
        $pointGroupId = $properties['group'] ?? null;
        $pointGroup   = $pointGroupId ? $this->groupModel->getEntity($pointGroupId) : null;
        $points       = $properties['points'];

        if ($pointGroup instanceof \MailVotech\PointBundle\Entity\Group) {
            $this->groupModel->adjustPoints($contact, $pointGroup, $points, $operator);
        } else {
            $contact->adjustPoints($points, $operator);
        }

        $newPoints = $contact->getPoints();

        $pointsChangeLog->setDelta($newPoints - $oldPoints);
        $contact->addPointsChangeLog($pointsChangeLog);

        $this->leadModel->saveEntity($contact, false);

        $event->getSubmission()->getLead()->setPoints($contact->getPoints());
    }

    public function onFormSubmitActionChangeList(SubmissionEvent $event): void
    {
        if (false === $event->checkContext('lead.changelist')) {
            return;
        }

        if (!$contact = $this->contactTracker->getContact()) {
            return;
        }

        $properties = $event->getAction()->getProperties();
        $addTo      = $properties['addToLists'] ?? null;
        $removeFrom = $properties['removeFromLists'] ?? null;

        if (!empty($addTo)) {
            $this->leadModel->addToLists($contact, $addTo);
        }

        if (!empty($removeFrom)) {
            $this->leadModel->removeFromLists($contact, $removeFrom);
        }
    }

    public function onFormSubmitActionChangeTags(SubmissionEvent $event): void
    {
        if (false === $event->checkContext('lead.changetags')) {
            return;
        }

        if (!$contact = $this->contactTracker->getContact()) {
            return;
        }

        $properties = $event->getAction()->getProperties();
        $addTags    = $properties['add_tags'] ?: [];
        $removeTags = $properties['remove_tags'] ?: [];

        $this->leadModel->modifyTags($contact, $addTags, $removeTags);
    }

    public function onFormSubmitActionAddUtmTags(SubmissionEvent $event): void
    {
        if (false === $event->checkContext('lead.addutmtags')) {
            return;
        }

        if (!$contact = $this->contactTracker->getContact()) {
            return;
        }

        $queryReferer = $queryArray = [];

        parse_str($event->getRequest()->server->get('QUERY_STRING'), $queryArray);
        $refererURL       = $event->getRequest()->server->get('HTTP_REFERER');
        $refererParsedUrl = parse_url($refererURL);

        if (isset($refererParsedUrl['query'])) {
            parse_str($refererParsedUrl['query'], $queryReferer);
        }

        $utmValues = new UtmTag();
        $utmValues->setLead($contact);
        $utmValues->setQuery($event->getRequest()->query->all());
        $utmValues->setReferer($refererURL);
        $utmValues->setUrl($event->getRequest()->server->get('REQUEST_URI'));
        $utmValues->setDateAdded(new \DateTime());
        $utmValues->setRemoteHost($refererParsedUrl['host'] ?? null);
        $utmValues->setUserAgent($event->getRequest()->server->get('HTTP_USER_AGENT') ?? null);
        $utmValues->setUtmCampaign($queryArray['utm_campaign'] ?? $queryReferer['utm_campaign'] ?? null);
        $utmValues->setUtmContent($queryArray['utm_content'] ?? $queryReferer['utm_content'] ?? null);
        $utmValues->setUtmMedium($queryArray['utm_medium'] ?? $queryReferer['utm_medium'] ?? null);
        $utmValues->setUtmSource($queryArray['utm_source'] ?? $queryReferer['utm_source'] ?? null);
        $utmValues->setUtmTerm($queryArray['utm_term'] ?? $queryReferer['utm_term'] ?? null);

        if ($utmValues->hasUtmTags()) {
            $this->leadModel->getUtmTagRepository()->saveEntity($utmValues);
            $this->leadModel->setUtmTags($utmValues->getLead(), $utmValues);
        }
    }

    public function onFormSubmitActionScoreContactsCompanies(SubmissionEvent $event): void
    {
        if (false === $event->checkContext('lead.scorecontactscompanies')) {
            return;
        }

        if (!$contact = $this->contactTracker->getContact()) {
            return;
        }

        $properties = $event->getActionConfig();

        if (!empty($properties['score'])) {
            $this->leadModel->scoreContactsCompany($contact, $properties['score']);
        }
    }

    public function onFormSubmitActionRemoveFromDoNotContact(SubmissionEvent $event): void
    {
        if (false === $event->checkContext('lead.remove_do_not_contact')) {
            return;
        }

        if ($event->getLead()) {
            $this->doNotContact->removeDncForContact($event->getLead()->getId(), 'email');
        }
    }

    public function onLeadSegmentsChange(SubmissionEvent $event): void
    {
        $properties = $event->getActionConfig();

        $lead       = $this->contactTracker->getContact();
        $addTo      = $properties['addToLists'];
        $removeFrom = $properties['removeFromLists'];

        if (!empty($addTo)) {
            $this->leadModel->addToLists($lead, $addTo);
        }

        if (!empty($removeFrom)) {
            $this->leadModel->removeFromLists($lead, $removeFrom);
        }
    }

    public function onFormSubmitActionUpdateLead(SubmissionEvent $event): void
    {
        if (false === $event->checkContext('lead.updatelead')) {
            return;
        }

        if (!$lead = $this->contactTracker->getContact()) {
            return;
        }

        $actionValues         = $event->getActionConfig();
        $contactFieldMatches  = $event->getContactFieldMatches();
        $fields               = $lead->getFields(true);

        $mergedValues = array_merge($actionValues, array_filter(
            $contactFieldMatches,
            static fn ($value): bool => '' !== $value && null !== $value
        ));

        $processedValues = [];
        foreach ($mergedValues as $alias => $value) {
            if (isset($fields[$alias]) && 'boolean' === $fields[$alias]['type'] && 0 === $value) {
                // 0 is interpreted as 'don't change the bool field' instead of setting it to false, so we change the field manually in this step
                $lead->addUpdatedField($alias, 0);
            }
            if (is_string($value)) {
                $processedValue = TokenHelper::findLeadTokens($value, $lead->getProfileFields(), true);
                $fieldEntity    = $this->leadFieldModel->getEntityByAlias($alias);

                if ($fieldEntity && ($charLimit = $fieldEntity->getCharLengthLimit()) && mb_strlen($processedValue) > $charLimit) {
                    $processedValue = mb_substr($processedValue, 0, $charLimit);
                }
                $processedValues[$alias] = $processedValue;
            } else {
                $processedValues[$alias] = $value;
            }
        }

        $this->leadModel->setFieldValues($lead, CustomFieldHelper::fieldsValuesTransformer($fields, $processedValues), false);
        $this->leadModel->saveEntity($lead);
    }
}
