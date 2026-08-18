<?php

namespace MailVotech\LeadBundle\EventListener;

use MailVotech\CampaignBundle\CampaignEvents;
use MailVotech\CampaignBundle\Entity\LeadEventLog;
use MailVotech\CampaignBundle\Event\CampaignBuilderEvent;
use MailVotech\CampaignBundle\Event\CampaignExecutionEvent;
use MailVotech\CampaignBundle\Event\PendingEvent;
use MailVotech\CampaignBundle\Model\CampaignModel;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Helper\IpLookupHelper;
use MailVotech\EmailBundle\Helper\UrlMatcher;
use MailVotech\LeadBundle\DataObject\LeadManipulator;
use MailVotech\LeadBundle\Entity\Company;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Entity\LeadDeviceRepository;
use MailVotech\LeadBundle\Entity\LeadFieldRepository;
use MailVotech\LeadBundle\Entity\LeadListRepository;
use MailVotech\LeadBundle\Entity\LeadRepository;
use MailVotech\LeadBundle\Entity\PointsChangeLog;
use MailVotech\LeadBundle\Exception\ImportFailedException;
use MailVotech\LeadBundle\Form\Type\AddToCompanyActionType;
use MailVotech\LeadBundle\Form\Type\CampaignConditionLeadPageHitType;
use MailVotech\LeadBundle\Form\Type\CampaignEventLeadAttachedType;
use MailVotech\LeadBundle\Form\Type\CampaignEventLeadCampaignsType;
use MailVotech\LeadBundle\Form\Type\CampaignEventLeadDeviceType;
use MailVotech\LeadBundle\Form\Type\CampaignEventLeadDNCType;
use MailVotech\LeadBundle\Form\Type\CampaignEventLeadFieldValueType;
use MailVotech\LeadBundle\Form\Type\CampaignEventLeadOwnerType;
use MailVotech\LeadBundle\Form\Type\CampaignEventLeadSegmentsType;
use MailVotech\LeadBundle\Form\Type\CampaignEventLeadStagesType;
use MailVotech\LeadBundle\Form\Type\CampaignEventLeadTagsType;
use MailVotech\LeadBundle\Form\Type\CampaignEventPointType;
use MailVotech\LeadBundle\Form\Type\ChangeOwnerType;
use MailVotech\LeadBundle\Form\Type\CompanyChangeScoreActionType;
use MailVotech\LeadBundle\Form\Type\ListActionType;
use MailVotech\LeadBundle\Form\Type\ModifyLeadTagsType;
use MailVotech\LeadBundle\Form\Type\PointActionType;
use MailVotech\LeadBundle\Form\Type\UpdateCompanyActionType;
use MailVotech\LeadBundle\Form\Type\UpdateLeadActionType;
use MailVotech\LeadBundle\Helper\CustomFieldHelper;
use MailVotech\LeadBundle\Helper\IdentifyCompanyHelper;
use MailVotech\LeadBundle\Helper\TokenHelper;
use MailVotech\LeadBundle\LeadEvents;
use MailVotech\LeadBundle\Model\CompanyModel;
use MailVotech\LeadBundle\Model\DoNotContact;
use MailVotech\LeadBundle\Model\FieldModel;
use MailVotech\LeadBundle\Model\LeadModel;
use MailVotech\LeadBundle\Provider\FilterOperatorProvider;
use MailVotech\LeadBundle\Segment\OperatorOptions;
use MailVotech\PointBundle\Model\PointGroupModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class CampaignSubscriber implements EventSubscriberInterface
{
    public const ACTION_LEAD_CHANGE_OWNER = 'lead.changeowner';

    private ?array $fields = null;

    public function __construct(
        private readonly IpLookupHelper $ipLookupHelper,
        private readonly LeadModel $leadModel,
        private readonly FieldModel $leadFieldModel,
        private readonly CompanyModel $companyModel,
        private readonly CampaignModel $campaignModel,
        private readonly CoreParametersHelper $coreParametersHelper,
        private readonly DoNotContact $doNotContact,
        private readonly PointGroupModel $groupModel,
        private readonly FilterOperatorProvider $filterOperatorProvider,
        private readonly LeadListRepository $leadListRepository,
        private readonly LeadRepository $leadRepository,
        private readonly LeadFieldRepository $leadFieldRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CampaignEvents::CAMPAIGN_ON_BUILD      => ['onCampaignBuild', 0],
            LeadEvents::ON_CAMPAIGN_TRIGGER_ACTION => [
                ['onCampaignTriggerActionChangePoints', 0],
                ['onCampaignTriggerActionChangeLists', 1],
                ['onCampaignTriggerActionUpdateTags', 3],
                ['onCampaignTriggerActionAddToCompany', 4],
                ['onCampaignTriggerActionChangeCompanyScore', 4],
                ['onCampaignTriggerActionChangeOwner', 7],
                ['onCampaignTriggerActionUpdateCompany', 8],
                ['onCampaignTriggerActionSetManipulator', 100],
            ],
            LeadEvents::ON_CAMPAIGN_BATCH_ACTION => [
                ['onCampaignTriggerActionUpdateLead', 0],
            ],
            LeadEvents::ON_CAMPAIGN_TRIGGER_CONDITION => [
                ['onCampaignTriggerCondition', 0],
            ],
        ];
    }

    /**
     * Add event triggers and actions.
     */
    public function onCampaignBuild(CampaignBuilderEvent $event): void
    {
        // Add actions
        $action = [
            'label'       => 'mailvotech.lead.lead.events.changepoints',
            'description' => 'mailvotech.lead.lead.events.changepoints_descr',
            'formType'    => PointActionType::class,
            'eventName'   => LeadEvents::ON_CAMPAIGN_TRIGGER_ACTION,
        ];
        $event->addAction('lead.changepoints', $action);

        $action = [
            'label'       => 'mailvotech.lead.lead.events.changelist',
            'description' => 'mailvotech.lead.lead.events.changelist_descr',
            'formType'    => ListActionType::class,
            'eventName'   => LeadEvents::ON_CAMPAIGN_TRIGGER_ACTION,
        ];
        $event->addAction('lead.changelist', $action);

        $action = [
            'label'          => 'mailvotech.lead.lead.events.updatelead',
            'description'    => 'mailvotech.lead.lead.events.updatelead_descr',
            'formType'       => UpdateLeadActionType::class,
            'formTheme'      => '@MailVotechLead/FormTheme/ActionUpdateLead/_updatelead_action_widget.html.twig',
            'batchEventName' => LeadEvents::ON_CAMPAIGN_BATCH_ACTION,
        ];
        $event->addAction('lead.updatelead', $action);

        $action = [
            'label'       => 'mailvotech.lead.lead.events.updatecompany',
            'description' => 'mailvotech.lead.lead.events.updatecompany_descr',
            'formType'    => UpdateCompanyActionType::class,
            'formTheme'   => '@MailVotechLead/FormTheme/ActionUpdateCompany/_updatecompany_action_widget.html.twig',
            'eventName'   => LeadEvents::ON_CAMPAIGN_TRIGGER_ACTION,
        ];
        $event->addAction('lead.updatecompany', $action);

        $action = [
            'label'       => 'mailvotech.lead.lead.events.changetags',
            'description' => 'mailvotech.lead.lead.events.changetags_descr',
            'formType'    => ModifyLeadTagsType::class,
            'eventName'   => LeadEvents::ON_CAMPAIGN_TRIGGER_ACTION,
        ];
        $event->addAction('lead.changetags', $action);

        $action = [
            'label'       => 'mailvotech.lead.lead.events.addtocompany',
            'description' => 'mailvotech.lead.lead.events.addtocompany_descr',
            'formType'    => AddToCompanyActionType::class,
            'eventName'   => LeadEvents::ON_CAMPAIGN_TRIGGER_ACTION,
        ];
        $event->addAction('lead.addtocompany', $action);

        $action = [
            'label'       => 'mailvotech.lead.lead.events.changeowner',
            'description' => 'mailvotech.lead.lead.events.changeowner_descr',
            'formType'    => ChangeOwnerType::class,
            'eventName'   => LeadEvents::ON_CAMPAIGN_TRIGGER_ACTION,
        ];
        $event->addAction(self::ACTION_LEAD_CHANGE_OWNER, $action);

        $action = [
            'label'       => 'mailvotech.lead.lead.events.changecompanyscore',
            'description' => 'mailvotech.lead.lead.events.changecompanyscore_descr',
            'formType'    => CompanyChangeScoreActionType::class,
            'eventName'   => LeadEvents::ON_CAMPAIGN_TRIGGER_ACTION,
        ];
        $event->addAction('lead.scorecontactscompanies', $action);

        $trigger = [
            'label'       => 'mailvotech.lead.lead.events.field_value',
            'description' => 'mailvotech.lead.lead.events.field_value_descr',
            'formType'    => CampaignEventLeadFieldValueType::class,
            'formTheme'   => '@MailVotechLead/FormTheme/FieldValueCondition/_campaignevent_lead_field_value_widget.html.twig',
            'eventName'   => LeadEvents::ON_CAMPAIGN_TRIGGER_CONDITION,
        ];
        $event->addCondition('lead.field_value', $trigger);

        $trigger = [
            'label'       => 'mailvotech.lead.lead.events.device',
            'description' => 'mailvotech.lead.lead.events.device_descr',
            'formType'    => CampaignEventLeadDeviceType::class,
            'eventName'   => LeadEvents::ON_CAMPAIGN_TRIGGER_CONDITION,
        ];

        $event->addCondition('lead.device', $trigger);

        $trigger = [
            'label'       => 'mailvotech.lead.lead.events.pageHit',
            'description' => 'mailvotech.lead.lead.events.pageHit_descr',
            'formType'    => CampaignConditionLeadPageHitType::class,
            'eventName'   => LeadEvents::ON_CAMPAIGN_TRIGGER_CONDITION,
        ];

        $event->addCondition('lead.pageHit', $trigger);

        $trigger = [
            'label'       => 'mailvotech.lead.lead.events.tags',
            'description' => 'mailvotech.lead.lead.events.tags_descr',
            'formType'    => CampaignEventLeadTagsType::class,
            'eventName'   => LeadEvents::ON_CAMPAIGN_TRIGGER_CONDITION,
        ];
        $event->addCondition('lead.tags', $trigger);

        $trigger = [
            'label'       => 'mailvotech.lead.lead.events.segments',
            'description' => 'mailvotech.lead.lead.events.segments_descr',
            'formType'    => CampaignEventLeadSegmentsType::class,
            'eventName'   => LeadEvents::ON_CAMPAIGN_TRIGGER_CONDITION,
        ];

        $event->addCondition('lead.segments', $trigger);

        $trigger = [
            'label'       => 'mailvotech.lead.lead.events.stages',
            'description' => 'mailvotech.lead.lead.events.stages_descr',
            'formType'    => CampaignEventLeadStagesType::class,
            'eventName'   => LeadEvents::ON_CAMPAIGN_TRIGGER_CONDITION,
        ];

        $event->addCondition('lead.stages', $trigger);

        $trigger = [
            'label'       => 'mailvotech.lead.lead.events.owner',
            'description' => 'mailvotech.lead.lead.events.owner_descr',
            'formType'    => CampaignEventLeadOwnerType::class,
            'eventName'   => LeadEvents::ON_CAMPAIGN_TRIGGER_CONDITION,
        ];

        $event->addCondition('lead.owner', $trigger);

        $trigger = [
            'label'       => 'mailvotech.lead.lead.events.attached',
            'description' => 'mailvotech.lead.lead.events.attached_descr',
            'formType'    => CampaignEventLeadAttachedType::class,
            'formTheme'   => '@MailVotechLead/FormTheme/ContactAddedCondition/_campaignevent_lead_contact_added_widget.html.twig',
            'eventName'   => LeadEvents::ON_CAMPAIGN_TRIGGER_CONDITION,
        ];

        $event->addCondition('lead.attached', $trigger);

        $trigger = [
            'label'       => 'mailvotech.lead.lead.events.campaigns',
            'description' => 'mailvotech.lead.lead.events.campaigns_descr',
            'formType'    => CampaignEventLeadCampaignsType::class,
            'formTheme'   => '@MailVotechLead/FormTheme/ContactCampaignsCondition/_campaignevent_lead_campaigns_widget.html.twig',
            'eventName'   => LeadEvents::ON_CAMPAIGN_TRIGGER_CONDITION,
        ];

        $event->addCondition('lead.campaigns', $trigger);

        $trigger = [
            'label'       => 'mailvotech.lead.lead.events.condition_donotcontact',
            'description' => 'mailvotech.lead.lead.events.condition_donotcontact_descr',
            'formType'    => CampaignEventLeadDNCType::class,
            'eventName'   => LeadEvents::ON_CAMPAIGN_TRIGGER_CONDITION,
        ];

        $event->addCondition('lead.dnc', $trigger);

        $trigger = [
            'label'       => 'mailvotech.lead.lead.events.points',
            'description' => 'mailvotech.lead.lead.events.points_descr',
            'formType'    => CampaignEventPointType::class,
            'eventName'   => LeadEvents::ON_CAMPAIGN_TRIGGER_CONDITION,
        ];

        $event->addCondition('lead.points', $trigger);
    }

    public function onCampaignTriggerActionChangePoints(CampaignExecutionEvent $event): void
    {
        if (!$event->checkContext('lead.changepoints')) {
            return;
        }

        $lead              = $event->getLead();
        $points            = $event->getConfig()['points'];
        $somethingHappened = false;

        if (null !== $lead && !empty($points)) {
            $pointsLogActionName      = "{$event->getEvent()['id']}: {$event->getEvent()['name']}";
            $pointsLogEventName       = "{$event->getEvent()['campaign']['id']}: {$event->getEvent()['campaign']['name']}";
            $pointGroupId             = $event->getConfig()['group'] ?? null;
            $pointGroup               = $pointGroupId ? $this->groupModel->getEntity($pointGroupId) : null;

            if ($pointGroup instanceof \MailVotech\PointBundle\Entity\Group) {
                $this->groupModel->adjustPoints($lead, $pointGroup, $points);
            } else {
                $lead->adjustPoints($points);
            }

            // add a lead point change log
            $log = new PointsChangeLog();
            $log->setDelta($points);
            $log->setLead($lead);
            $log->setType('campaign');
            $log->setEventName($pointsLogEventName);
            $log->setActionName($pointsLogActionName);
            $log->setIpAddress($this->ipLookupHelper->getIpAddress());
            $log->setDateAdded(new \DateTime());
            if ($pointGroup) {
                $log->setGroup($pointGroup);
            }
            $lead->addPointsChangeLog($log);

            $this->leadModel->saveEntity($lead);
            $somethingHappened = true;
        }

        $event->setResult($somethingHappened);
    }

    public function onCampaignTriggerActionChangeLists(CampaignExecutionEvent $event): void
    {
        if (!$event->checkContext('lead.changelist')) {
            return;
        }

        $addTo      = $event->getConfig()['addToLists'];
        $removeFrom = $event->getConfig()['removeFromLists'];

        $lead              = $event->getLead();
        $somethingHappened = false;

        if (!empty($addTo)) {
            $this->leadModel->addToLists($lead, $addTo);
            $somethingHappened = true;
        }

        if (!empty($removeFrom)) {
            $this->leadModel->removeFromLists($lead, $removeFrom);
            $somethingHappened = true;
        }

        $event->setResult($somethingHappened);
    }

    public function onCampaignTriggerActionUpdateLead(PendingEvent $event): void
    {
        $values = $event->getEvent()->getProperties();
        if (!$event->checkContext('lead.updatelead')) {
            return;
        }

        $logs = $event->getPending();

        foreach ($logs as $log) {
            $this->updateLead($log, $values, $event);
        }
    }

    /**
     * @param array<mixed> $values
     */
    private function updateLead(LeadEventLog $log, array $values, PendingEvent $event): void
    {
        $lead   = $log->getLead();
        $fields = $lead->getFields(true);

        try {
            $tokenizedValues = [];
            foreach ($values as $field => $value) {
                if (is_string($value)) {
                    $tokenizedValue = TokenHelper::findLeadTokens($value, $lead->getProfileFields(), true);
                    $fieldEntity    = $this->leadFieldModel->getEntityByAlias($field);
                    if ($fieldEntity && ($charLimit = $fieldEntity->getCharLengthLimit()) && mb_strlen($tokenizedValue) > $charLimit) {
                        $tokenizedValue = mb_substr($tokenizedValue, 0, $charLimit);
                    }
                    $tokenizedValues[$field] = $tokenizedValue;
                } else {
                    $tokenizedValues[$field] = $value;
                }
            }
            $this->leadModel->setFieldValues($lead, CustomFieldHelper::fieldsValuesTransformer($fields, $tokenizedValues), false);
        } catch (ImportFailedException $e) {
            $event->fail($log, $e->getMessage());
        }

        foreach ($values as $alias => &$value) {
            if (isset($fields[$alias]) && 'boolean' === $fields[$alias]['type'] && 0 === $value) {
                // 0 is interpreted as 'don't change the bool field' instead of setting it to false, so we change the field manually in this step
                $lead->addUpdatedField($alias, 0);
            }
        }

        $this->leadModel->setFieldValues($lead, CustomFieldHelper::fieldsValuesTransformer($fields, $tokenizedValues), false);
        $this->leadModel->saveEntity($lead);
        $event->pass($log);
    }

    public function onCampaignTriggerActionChangeOwner(CampaignExecutionEvent $event): void
    {
        if (!$event->checkContext(self::ACTION_LEAD_CHANGE_OWNER)) {
            return;
        }

        $lead = $event->getLead();
        $data = $event->getConfig();
        if (empty($data['owner'])) {
            return;
        }

        $this->leadModel->updateLeadOwner($lead, $data['owner']);

        $event->setResult(true);
    }

    public function onCampaignTriggerActionUpdateTags(CampaignExecutionEvent $event): void
    {
        if (!$event->checkContext('lead.changetags')) {
            return;
        }

        $config = $event->getConfig();
        $lead   = $event->getLead();

        $addTags    = (!empty($config['add_tags'])) ? $config['add_tags'] : [];
        $removeTags = (!empty($config['remove_tags'])) ? $config['remove_tags'] : [];

        $this->leadModel->modifyTags($lead, $addTags, $removeTags);

        $event->setResult(true);
    }

    public function onCampaignTriggerActionAddToCompany(CampaignExecutionEvent $event): void
    {
        if (!$event->checkContext('lead.addtocompany')) {
            return;
        }

        $company = $event->getConfig()['company'];
        $lead    = $event->getLead();

        if (!empty($company)) {
            $this->leadModel->addToCompany($lead, $company);
        }
    }

    public function onCampaignTriggerActionChangeCompanyScore(CampaignExecutionEvent $event): void
    {
        if (!$event->checkContext('lead.scorecontactscompanies')) {
            return;
        }

        $score = $event->getConfig()['score'];
        $lead  = $event->getLead();

        if (!$this->leadModel->scoreContactsCompany($lead, $score)) {
            $event->setFailed('mailvotech.lead.no_company');

            return;
        }

        $event->setResult(true);
    }

    public function onCampaignTriggerActionUpdateCompany(CampaignExecutionEvent $event): void
    {
        if (!$event->checkContext('lead.updatecompany')) {
            return;
        }

        $lead    = $event->getLead();
        $company = $lead->getPrimaryCompany();
        $config  = $event->getConfig();

        if (empty($company['id'])) {
            return;
        }

        $primaryCompany =  $this->companyModel->getEntity($company['id']);

        if (isset($config['companyname']) && $primaryCompany->getName() != $config['companyname']) {
            [$company, $leadAdded, $companyEntity] = IdentifyCompanyHelper::identifyLeadsCompany($config, $lead, $this->companyModel);
            $companyChangeLog                      = null;
            if ($leadAdded) {
                $companyChangeLog = $lead->addCompanyChangeLogEntry('form', 'Identify Company', 'Lead added to the company, '.$company['companyname'], $company['id']);
            } elseif ($companyEntity instanceof Company) {
                $this->companyModel->setFieldValues($companyEntity, $config);
                $this->companyModel->saveEntity($companyEntity);
            }

            if (!empty($company)) {
                // Save after the lead in for new leads created
                $this->companyModel->addLeadToCompany($companyEntity, $lead);
                $this->leadModel->setPrimaryCompany($companyEntity->getId(), $lead->getId());
            }

            if (null !== $companyChangeLog) {
                $this->companyModel->getCompanyLeadRepository()->detachEntity($companyChangeLog);
            }
        } else {
            $this->companyModel->setFieldValues($primaryCompany, $config, false);
            $this->companyModel->saveEntity($primaryCompany);
        }

        $event->setResult(true);
    }

    public function onCampaignTriggerCondition(CampaignExecutionEvent $event): void
    {
        $lead   = $event->getLead();
        $result = false;

        if (!$lead || !$lead->getId()) {
            $event->setResult(false);

            return;
        }

        if ($event->checkContext('lead.device')) {
            $result = $this->validateContactDevice($event, $lead, $this->leadModel->getDeviceRepository());
        } elseif ($event->checkContext('lead.tags')) {
            $tagRepo = $this->leadModel->getTagRepository();
            $result  = $tagRepo->checkLeadByTags($lead, $event->getConfig()['tags']);
        } elseif ($event->checkContext('lead.segments')) {
            $result = $this->leadListRepository->checkLeadSegmentsByIds($lead, $event->getConfig()['segments']);
        } elseif ($event->checkContext('lead.stages')) {
            $result   = $this->leadRepository->isContactInOneOfStages($lead, $event->getConfig()['stages']);
        } elseif ($event->checkContext('lead.owner')) {
            $result = $this->leadRepository->checkLeadOwner($lead, $event->getConfig()['owner']);
        } elseif ($event->checkContext('lead.attached')) {
            $result = $this->onCampaignTriggerConditionContactAdded($event);
        } elseif ($event->checkContext('lead.campaigns')) {
            $result = $this->campaignModel->getCampaignLeadRepository()->checkLeadInCampaigns($lead, $event->getConfig());
        } elseif ($event->checkContext('lead.field_value')) {
            if ('date' === $event->getConfig()['operator']) {
                // Set the date in system timezone since this is triggered by cron
                $triggerDate = new \DateTime('now',
                    new \DateTimeZone($this->coreParametersHelper->getDefaultTimezone()));
                $interval    = substr($event->getConfig()['value'], 1); // remove 1st character + or -

                if (str_contains($event->getConfig()['value'], '+P')) { // add date
                    $triggerDate->add(new \DateInterval($interval)); // add the today date with interval
                    $result = $this->compareDateValue($lead, $event, $triggerDate);
                } elseif (str_contains($event->getConfig()['value'], '-P')) { // subtract date
                    $triggerDate->sub(new \DateInterval($interval)); // subtract the today date with interval
                    $result = $this->compareDateValue($lead, $event, $triggerDate);
                } elseif ('anniversary' === $event->getConfig()['value']) {
                    /**
                     * note: currently mailvotech campaign only one time execution
                     * ( to integrate with: recursive campaign (future)).
                     */
                    $result = $this->leadFieldRepository->compareDateMonthValue(
                        $lead->getId(), $event->getConfig()['field'], $triggerDate);
                }
            } else {
                $operators = OperatorOptions::getFilterExpressionFunctions();
                $field     = $event->getConfig()['field'];
                $value     = $event->getConfig()['value'] ?? '';
                $operator  = $event->getConfig()['operator'];
                $fields    = $this->getFields($lead);

                $fieldType  = '';
                $fieldValue = $value;
                if (isset($fields[$field])) {
                    $fieldType  = $fields[$field]['type'];
                    // Keep regex values unchanged so they are evaluated as patterns
                    // Otherwise CustomFieldHelper::fieldValueTransfomer would attempt to parse
                    // the regex as a DateTime string, which would throw an error
                    if (!in_array($operator, [OperatorOptions::REGEXP, OperatorOptions::NOT_REGEXP])) {
                        $fieldValue = CustomFieldHelper::fieldValueTransfomer($fields[$field], $value);
                    }
                }

                // Preventing date/datetime fields to fail on empty/notEmpty
                if (in_array($fieldType, ['date', 'datetime']) && in_array($operator, ['empty', '!empty'])) {
                    $result     = $this->leadFieldRepository->compareEmptyDateValue(
                        $lead->getId(),
                        $field,
                        $operators[$operator]['expr']
                    );
                } else {
                    $result     = $this->leadFieldRepository->compareValue(
                        $lead->getId(),
                        $field,
                        $fieldValue,
                        $operators[$operator]['expr']
                    );

                    $log = $event->getLogEntry();
                    if (null !== $log) {
                        $log->setMetadata([
                            'comparisonValue' => $fieldValue,
                            'operator'        => $operators[$event->getConfig()['operator']]['expr'],
                            'value'           => $value,
                            'field'           => $field,
                        ]);
                    }
                }
            }
        } elseif ($event->checkContext('lead.dnc')) {
            $channels  = $event->getConfig()['channels'];
            $reason    = $event->getConfig()['reason'] ?? null;
            foreach ($channels as $channel) {
                $isLeadDNC = $this->doNotContact->isContactable($lead, $channel);
                if (!empty($reason)) {
                    if ($isLeadDNC === $reason) {
                        $result = true;
                    } else {
                        $result = false;
                    }
                } else {
                    if (0 !== $isLeadDNC) {
                        $result = true;
                    } else {
                        $result = false;
                    }
                }
            }
        } elseif ($event->checkContext('lead.pageHit')) {
            $startDate = $event->getConfig()['startDate'] ?? null;
            $endDate   = $event->getConfig()['endDate'] ?? null;
            $page      = $event->getConfig()['page'] ?? null;
            $url       = $event->getConfig()['page_url'] ?? null;

            $filter = [
                'search'        => '',
                'includeEvents' => [
                    0 => 'page.hit',
                ],
                'excludeEvents' => [],
            ];

            if ($startDate) {
                if (!is_a($startDate, 'DateTime')) {
                    $startDate = new \DateTime($startDate);
                }
                $filter['dateFrom'] = $startDate;
            }

            if ($endDate) {
                if (!is_a($endDate, 'DateTime')) {
                    $endDate = new \DateTime($endDate);
                }
                $filter['dateTo'] = $endDate->modify('+1 minutes');
            }

            $orderby = [
                0 => 'timestamp',
                1 => 'DESC',
            ];

            $leadTimeline       = $this->leadModel->getEngagements($lead, $filter, $orderby, 1, 255, false);
            $totalSpentTime     = $event->getConfig()['accumulative_time'] ?? null;
            $eventsLeadTimeline = $leadTimeline[0]['events'] ?? null;
            if (!empty($eventsLeadTimeline)) {
                foreach ($eventsLeadTimeline as $eventLeadTimeline) {
                    $hit        = $eventLeadTimeline['details']['hit'] ?? null;
                    $pageHitUrl = $hit['url'] ?? null;
                    $pageId     = $hit['page_id'] ?? null;

                    if (!empty($url)) {
                        $pageUrl = html_entity_decode($pageHitUrl);
                        if (UrlMatcher::hasMatch([$url], $pageUrl)) {
                            if ($hit['dateLeft'] && $totalSpentTime) {
                                $realTotalSpentTime = (new \DateTime($hit['dateLeft']->format('Y-m-d H:i')))->getTimestamp() -
                                    (new \DateTime($hit['dateHit']->format('Y-m-d H:i')))->getTimestamp();
                                if ($realTotalSpentTime >= $totalSpentTime) {
                                    $event->setResult(true);

                                    return;
                                }
                            } elseif (!$totalSpentTime) {
                                $event->setResult(true);

                                return;
                            }
                        }
                    }

                    if (!empty($page) && (int) $page === (int) $pageId) {
                        if ($hit['dateLeft'] && $totalSpentTime) {
                            $realTotalSpentTime = (new \DateTime($hit['dateLeft']->format('Y-m-d H:i')))->getTimestamp() -
                                (new \DateTime($hit['dateHit']->format('Y-m-d H:i')))->getTimestamp();
                            if ($realTotalSpentTime >= $totalSpentTime) {
                                $event->setResult(true);

                                return;
                            }
                        } elseif (!$totalSpentTime) {
                            $event->setResult(true);

                            return;
                        }
                    }
                }
            }
        } elseif ($event->checkContext('lead.points')) {
            $operators    = $this->filterOperatorProvider->getAllOperators();
            $group        = $event->getConfig()['group'] ?? null;
            $score        = $event->getConfig()['score'];
            $operatorExpr = $operators[$event->getConfig()['operator']]['expr'];

            if ($group) {
                $result = $this->leadModel->getGroupContactScoreRepository()->compareScore(
                    $lead->getId(), $group, $score, $operatorExpr,
                );
            } else {
                $result = $this->leadFieldRepository->compareValue(
                    $lead->getId(), 'points', $score, $operatorExpr
                );
            }
        }

        $event->setResult($result);
    }

    /**
     * @throws \Exception
     */
    /**
     * @phpstan-ignore-next-line
     */
    public function onCampaignTriggerConditionContactAdded(CampaignExecutionEvent $event): bool
    {
        $campaign = $this->campaignModel->getEntity($event->getEvent()['campaign']['id']);

        $campaignExecutionEventConfig = $event->getConfig();

        $timestamp              = $campaignExecutionEventConfig['timestamp'] ?? null;
        $operator               = $campaignExecutionEventConfig['operator'] ?? null;
        $triggerInterval        = $campaignExecutionEventConfig['triggerInterval'] ?? null;
        $triggerIntervalUnit    = $campaignExecutionEventConfig['triggerIntervalUnit'] ?? null;

        // You may replace if statement to switch and the following code to private function when multiple options available
        if ('campaign_start_date' !== $timestamp) {
            return false;
        }

        $publishUp        = $campaign->getPublishUp();
        $dateAdded        = $campaign->getDateAdded();

        $objEffectiveDate = ($publishUp instanceof \DateTime) ? $publishUp : $dateAdded;
        if (!$objEffectiveDate instanceof \DateTime) {
            return false;
        }

        $triggerIntervalUnit = strtoupper($triggerIntervalUnit);
        $timeNotation        = '';
        // add T for Time units
        if (in_array($triggerIntervalUnit, ['H', 'I'])) {
            $timeNotation = 'T';
            // DateInterval Minutes notation is 'M'
            $triggerIntervalUnit = ('I' === $triggerIntervalUnit) ? 'M' : $triggerIntervalUnit;
        }

        $duration = 'P'.$timeNotation.$triggerInterval.$triggerIntervalUnit;

        $interval         = new \DateInterval($duration);
        $objEffectiveDate = clone $objEffectiveDate;
        $objEffectiveDate->add($interval);

        $now    = new \DateTime();
        if (OperatorOptions::LESS_THAN == $operator) {
            $result = ($now < $objEffectiveDate);
        } else {
            $result = ($now > $objEffectiveDate);
        }

        return $result;
    }

    public function onCampaignTriggerActionSetManipulator(CampaignExecutionEvent $event): void
    {
        $lead = $event->getLead();

        if (!$lead instanceof Lead) {
            return;
        }

        $campaign      = $event->getLogEntry()->getCampaign();
        $campaignEvent = $event->getLogEntry()->getEvent();

        $lead->setManipulator(
            new LeadManipulator(
                'campaign',
                'trigger-action',
                $campaignEvent->getId(),
                sprintf('%s (%s)', $campaignEvent->getName(), $campaign->getName())
            )
        );
    }

    /**
     * Function to compare date value.
     */
    private function compareDateValue(Lead $lead, CampaignExecutionEvent $event, \DateTime $triggerDate): bool
    {
        return $this->leadFieldRepository->compareDateValue(
            $lead->getId(),
            $event->getConfig()['field'],
            $triggerDate->format('Y-m-d')
        );
    }

    private function getFields(Lead $lead): array
    {
        if (!$this->fields) {
            $contactFields = $lead->getFields(true);
            $companyFields = $this->leadFieldModel->getFieldListWithProperties('company');
            $this->fields  = array_merge($contactFields, $companyFields);
        }

        return $this->fields;
    }

    /**
     * It returns true if contact device matches
     * device specified in the
     * CampaignExecutionEvent's settings.
     */
    private function validateContactDevice(CampaignExecutionEvent $campaignExecutionEvent, Lead $contact, LeadDeviceRepository $leadDeviceRepository): bool // @phpstan-ignore parameter.deprecatedClass
    {
        $campaignExecutionEventConfig = $campaignExecutionEvent->getConfig();
        $deviceType                   = empty($campaignExecutionEventConfig['device_type']) ? null : $campaignExecutionEventConfig['device_type'];
        $deviceBrands                 = empty($campaignExecutionEventConfig['device_brand']) ? null : $campaignExecutionEventConfig['device_brand'];
        $deviceOs                     = empty($campaignExecutionEventConfig['device_os']) ? null : $campaignExecutionEventConfig['device_os'];

        return !empty($leadDeviceRepository->getDevice($contact, $deviceType, $deviceBrands, null, $deviceOs));
    }
}
