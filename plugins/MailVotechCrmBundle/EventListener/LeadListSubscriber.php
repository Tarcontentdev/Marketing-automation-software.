<?php

namespace MailVotechPlugin\MailVotechCrmBundle\EventListener;

use MailVotech\LeadBundle\Event\LeadListFiltersChoicesEvent;
use MailVotech\LeadBundle\Event\ListPreProcessListEvent;
use MailVotech\LeadBundle\Helper\FormFieldHelper;
use MailVotech\LeadBundle\LeadEvents;
use MailVotech\LeadBundle\Model\ListModel;
use MailVotech\PluginBundle\Helper\IntegrationHelper;
use MailVotechPlugin\MailVotechCrmBundle\Integration\CrmAbstractIntegration;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class LeadListSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private IntegrationHelper $helper,
        private ListModel $listModel,
        private TranslatorInterface $translator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LeadEvents::LIST_FILTERS_CHOICES_ON_GENERATE => ['onFilterChoiceFieldsGenerate', 0],
            LeadEvents::LIST_PRE_PROCESS_LIST            => ['onLeadListProcessList', 0],
        ];
    }

    public function onFilterChoiceFieldsGenerate(LeadListFiltersChoicesEvent $event): void
    {
        $services = $this->helper->getIntegrationObjects();
        $choices  = [];

        /** @var CrmAbstractIntegration $integration */
        foreach ($services as $integration) {
            if (!$integration || !$integration->getIntegrationSettings()->isPublished()) {
                continue;
            }

            if (method_exists($integration, 'getCampaigns')) {
                $integrationChoices = $integration->getCampaignChoices();
                if ($integrationChoices) {
                    $integrationName = $integration->getName();
                    // Keep BC with pre-2.11.0 that only supported SF campaigns
                    if ('Salesforce' !== $integrationName) {
                        array_walk(
                            $integrationChoices,
                            function (array &$choice) use ($integrationName): void {
                                $choice['value'] = $integrationName.'::'.$choice['value'];
                            }
                        );
                    }
                    $integrationChoices                      = FormFieldHelper::parseListForChoices($integrationChoices);
                    $choices[$integration->getDisplayName()] = $integrationChoices;
                    $choices[$integration->getDisplayName()] = array_combine(
                        array_column($integrationChoices, 'label'),
                        array_column($integrationChoices, 'value')
                    );
                }
            }
        }

        if ([] !== $choices) {
            $config = [
                'label'      => $this->translator->trans('mailvotech.plugin.integration.campaign_members'),
                'properties' => ['type' => 'select', 'list' => $choices],
                'operators'  => $this->listModel->getOperatorsForFieldType(
                    [
                        'include' => [
                            '=',
                        ],
                    ]
                ),
                'object' => 'lead',
            ];
            $event->addChoice('lead', 'integration_campaigns', $config);
        }
    }

    /**
     * Add/remove contacts to a segment based on contacts found in Integration Campaigns.
     */
    public function onLeadListProcessList(ListPreProcessListEvent $event): void
    {
        // get Integration Campaign members
        $list    = $event->getList();
        $success = false;
        $filters = $list['filters'];

        foreach ($filters as $filter) {
            if ('integration_campaigns' == $filter['field']) {
                if (str_contains($filter['filter'], '::')) {
                    [$integrationName, $campaignId] = explode('::', $filter['filter']);
                } else {
                    // Assuming this is a Salesforce integration for BC with pre 2.11.0
                    $integrationName = 'Salesforce';
                    $campaignId      = $filter['filter'];
                }

                /** @var CrmAbstractIntegration $integrationObject */
                if ($integrationObject = $this->helper->getIntegrationObject($integrationName)) {
                    if (!$integrationObject->getIntegrationSettings()->isPublished()) {
                        continue;
                    }

                    if (method_exists($integrationObject, 'getCampaignMembers')) {
                        if ($integrationObject->getCampaignMembers($campaignId)) {
                            $success = true;
                        }
                    }
                }
            }
        }

        $event->setResult($success);
    }
}
