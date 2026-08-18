<?php

namespace MailVotech\ReportBundle\Event;

use Doctrine\ORM\Tools\Pagination\Paginator;
use MailVotech\ChannelBundle\Helper\ChannelListHelper;
use MailVotech\ReportBundle\Builder\MailVotechReportBuilder;
use MailVotech\ReportBundle\Helper\ReportHelper;
use MailVotech\ReportBundle\Model\ReportModel;
use Symfony\Contracts\Translation\TranslatorInterface;

class ReportBuilderEvent extends AbstractReportEvent
{
    /**
     * Container with registered tables and columns.
     */
    private array $tableArray = [];

    /**
     * @var string[]
     */
    private array $supportedGraphs = [
        'table',
        'bar',
        'pie',
        'line',
    ];

    /**
     * @var mixed[]
     */
    private array $graphArray = [];

    /**
     * @param mixed[]|Paginator|array $leadFields list of published array of lead fields
     */
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly ChannelListHelper $channelListHelper,
        string $context,
        private readonly array|Paginator $leadFields,
        private readonly ReportHelper $reportHelper,
        private readonly ?string $reportSource = null,
    ) {
        $this->context = $context;
    }

    /**
     * Add a table with the specified columns to the lookup.
     *
     * The data should be an associative array with the following data:
     * 'display_name' => The translation key to display in the select list
     * 'columns'      => An array containing the table's columns
     *
     * @param string $context Context for data
     * @param array  $data    Data array for the table
     */
    public function addTable($context, array $data, $group = null): static
    {
        $data['group'] = (null == $group) ? $context : $group;

        foreach ($data['columns'] as $column => &$d) {
            $d['label'] = null !== $d['label'] ? $this->translator->trans($d['label']) : '';
            if (!isset($d['alias'])) {
                $d['alias'] = substr(
                    $column,
                    false !== ($pos = strpos($column, '.')) ? $pos + 1 : 0
                );
            }
        }

        uasort($data['columns'], fn ($a, $b): int => strnatcmp((string) $a['label'], (string) $b['label']));

        if (isset($data['filters'])) {
            foreach ($data['filters'] as $column => &$d) {
                $d['label'] = $this->translator->trans($d['label']);
                if (!isset($d['alias'])) {
                    $d['alias'] = substr(
                        $column,
                        false !== ($pos = strpos($column, '.')) ? $pos + 1 : 0
                    );
                }
            }

            uasort($data['filters'], fn ($a, $b): int => strnatcmp((string) $a['label'], (string) $b['label']));
        }

        $this->tableArray[$context] = $data;

        if ($this->context == $context) {
            $this->stopPropagation();
        }

        return $this;
    }

    /**
     * Fetch the tables in the lookup array.
     */
    public function getTables(): array
    {
        return $this->tableArray;
    }

    /**
     * Fetch the source of the report.
     */
    public function getReportSource(): ?string
    {
        return $this->reportSource;
    }

    /**
     * Returns standard form fields such as id, name, publish_up, etc.
     *
     * @param string[] $removeColumns
     *
     * @return array<string,array<string,string>>
     */
    public function getStandardColumns(string $prefix, array $removeColumns = [], $idLink = null): array
    {
        return $this->reportHelper->getStandardColumns($prefix, $removeColumns, (string) $idLink);
    }

    /**
     * Returns lead columns.
     */
    public function getLeadColumns($prefix = 'l.'): array
    {
        $fields = [];

        foreach ($this->leadFields as $fieldArray) {
            $fields[$prefix.$fieldArray['alias']] = [
                'label' => $this->translator->trans('mailvotech.report.field.lead.label', ['%field%' => $fieldArray['label']]),
                'type'  => $this->reportHelper->getReportBuilderFieldType($fieldArray['type']),
                'alias' => $fieldArray['alias'],
            ];
        }
        $fields[$prefix.'id'] = [
            'label' => 'mailvotech.report.field.lead.id',
            'type'  => 'int',
            'link'  => 'mailvotech_contact_action',
            'alias' => 'contactId',
        ];

        return $fields;
    }

    /**
     * Get IP Address column.
     *
     * @param string $prefix
     */
    public function getIpColumn($prefix = 'i.'): array
    {
        return [
            $prefix.'ip_address' => [
                'label' => 'mailvotech.core.ipaddress',
                'type'  => 'string',
            ],
        ];
    }

    /**
     * Add category columns.
     *
     * @param string $prefix
     */
    public function getCategoryColumns($prefix = 'c.'): array
    {
        return [
            $prefix.'id' => [
                'label' => 'mailvotech.report.field.category_id',
                'type'  => 'int',
                'alias' => 'category_id',
            ],
            $prefix.'title' => [
                'label' => 'mailvotech.report.field.category_name',
                'type'  => 'string',
                'alias' => 'category_title',
            ],
        ];
    }

    /**
     * Add campaign columns joined by the campaign lead event log table.
     */
    public function getCampaignByChannelColumns(): array
    {
        return [
            'clel.campaign_id' => [
                'label' => 'mailvotech.campaign.campaign.id',
                'type'  => 'string',
            ],
            'cmp.name' => [
                'label' => 'mailvotech.campaign.campaign',
                'type'  => 'string',
            ],
        ];
    }

    /**
     * @return array<MailVotechReportBuilder::*, mixed[]>
     */
    public function getChannelColumns(): array
    {
        $channelColumns = [
            MailVotechReportBuilder::CHANNEL_COLUMN_CATEGORY_ID => [
                'label'       => 'mailvotech.report.campaign.channel.category_id',
                'type'        => 'int',
                'alias'       => 'channel_category_id',
                'channelData' => [],
            ],
            MailVotechReportBuilder::CHANNEL_COLUMN_CREATED_BY => [
                'label'       => 'mailvotech.report.campaign.channel.created_by',
                'type'        => 'int',
                'alias'       => 'channel_created_by',
                'channelData' => [],
            ],
            MailVotechReportBuilder::CHANNEL_COLUMN_CREATED_BY_USER => [
                'label'       => 'mailvotech.report.campaign.channel.created_by_user',
                'type'        => 'string',
                'alias'       => 'channel_created_by_user',
                'channelData' => [],
            ],
            MailVotechReportBuilder::CHANNEL_COLUMN_DATE_ADDED => [
                'label'       => 'mailvotech.report.campaign.channel.date_added',
                'type'        => 'datetime',
                'alias'       => 'channel_date_added',
                'channelData' => [],
            ],
            MailVotechReportBuilder::CHANNEL_COLUMN_DESCRIPTION => [
                'label'       => 'mailvotech.report.campaign.channel.description',
                'type'        => 'string',
                'alias'       => 'channel_description',
                'channelData' => [],
            ],
            MailVotechReportBuilder::CHANNEL_COLUMN_NAME => [
                'label'       => 'mailvotech.report.campaign.channel.name',
                'type'        => 'string',
                'alias'       => 'channel_name',
                'channelData' => [],
            ],
        ];

        foreach ($this->channelListHelper->getChannels() as $channel => $details) {
            if (!array_key_exists(ReportModel::CHANNEL_FEATURE, $details)) {
                continue;
            }

            $reportDetails = $details[ReportModel::CHANNEL_FEATURE];

            $hasFields = array_key_exists('fields', $reportDetails) && is_array($reportDetails['fields']);

            foreach ($channelColumns as $column => $definition) {
                $channelColumnName = $hasFields && array_key_exists($column, $reportDetails['fields'])
                    ? $reportDetails['fields'][$column]
                    : str_replace('channel.', $channel.'.', $column);

                $channelColumns[$column]['channelData'][$channel] = [
                    'prefix' => $channel,
                    'column' => $channelColumnName,
                ];
            }
        }

        return $channelColumns;
    }

    /**
     * @param array $options
     */
    public function addGraph($context, $type, $graphId, $options = []): static
    {
        if (in_array($type, $this->supportedGraphs)) {
            $this->graphArray[$context][$graphId] = [
                'options' => $options,
                'type'    => $type,
            ];
        }

        return $this;
    }

    public function getGraphs(): array
    {
        return $this->graphArray;
    }
}
