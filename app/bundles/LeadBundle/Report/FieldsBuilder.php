<?php

namespace MailVotech\LeadBundle\Report;

use MailVotech\LeadBundle\Entity\LeadField;
use MailVotech\LeadBundle\Model\FieldModel;
use MailVotech\LeadBundle\Model\LeadModel;
use MailVotech\LeadBundle\Model\ListModel;
use MailVotech\UserBundle\Model\UserModel;

class FieldsBuilder
{
    public function __construct(
        private readonly FieldModel $fieldModel,
        private readonly ListModel $listModel,
        private readonly UserModel $userModel,
        private readonly LeadModel $leadModel,
        private readonly DncReportService $dncReportService,
    ) {
    }

    /**
     * @param string $prefix
     */
    public function getLeadFieldsColumns($prefix): array
    {
        $baseColumns  = $this->getBaseLeadColumns();
        $leadFields   = $this->fieldModel->getLeadFields();
        $fieldColumns = $this->getFieldColumns($leadFields, $prefix);

        return array_merge($baseColumns, $fieldColumns);
    }

    /**
     * @param string $prefix
     * @param string $segmentPrefix
     */
    public function getLeadFilter($prefix, $segmentPrefix): array
    {
        $filters = $this->getLeadFieldsColumns($prefix);

        $segmentPrefix = $this->sanitizePrefix($segmentPrefix);
        $prefix        = $this->sanitizePrefix($prefix);

        // Append segment filters
        $userSegments = $this->listModel->getUserLists();

        $list = [];
        foreach ($userSegments as $segment) {
            $list[$segment['id']] = $segment['name'];
        }

        $segmentKey           = $segmentPrefix.'leadlist_id';
        $filters[$segmentKey] = [
            'alias'     => 'segment_id',
            'label'     => 'mailvotech.core.filter.lists',
            'type'      => 'select',
            'list'      => $list,
            'operators' => [
                'eq' => 'mailvotech.core.operator.equals',
            ],
        ];

        $aTags     = [];
        $aTagsList = $this->leadModel->getTagList();
        foreach ($aTagsList as $aTemp) {
            $aTags[$aTemp['value']] = $aTemp['label'];
        }

        $filters['tag'] = [
            'label'     => 'mailvotech.core.filter.tags',
            'type'      => 'multiselect',
            'list'      => $aTags,
            'operators' => [
                'in'       => 'mailvotech.core.operator.in',
                'notIn'    => 'mailvotech.core.operator.notin',
                'empty'    => 'mailvotech.core.operator.isempty',
                'notEmpty' => 'mailvotech.core.operator.isnotempty',
            ],
        ];

        // Add DNC Status filter
        $filters = array_merge($filters, $this->dncReportService->getDncFilters());

        $ownerPrefix           = $prefix.'owner_id';
        $ownersList            = [];
        $owners                = $this->userModel->getUserList('', 0);
        foreach ($owners as $owner) {
            $ownersList[$owner['id']] = sprintf('%s %s', $owner['firstName'], $owner['lastName']);
        }
        $filters[$ownerPrefix] = [
            'label' => 'mailvotech.lead.list.filter.owner',
            'type'  => 'select',
            'list'  => $ownersList,
        ];

        return $filters;
    }

    /**
     * @param string $prefix
     */
    public function getCompanyFieldsColumns($prefix): array
    {
        $baseColumns   = $this->getBaseCompanyColumns();
        $companyFields = $this->fieldModel->getCompanyFields();
        $fieldColumns  = $this->getFieldColumns($companyFields, $prefix);

        return array_merge($baseColumns, $fieldColumns);
    }

    private function getBaseLeadColumns(): array
    {
        return [
            'l.id' => [
                'label' => 'mailvotech.lead.report.contact_id',
                'type'  => 'int',
                'link'  => 'mailvotech_contact_action',
            ],
            'i.ip_address' => [
                'label' => 'mailvotech.core.ipaddress',
                'type'  => 'text',
            ],
            'l.date_identified' => [
                'label'          => 'mailvotech.lead.report.date_identified',
                'type'           => 'datetime',
                'groupByFormula' => 'DATE(l.date_identified)',
            ],
            'l.date_added' => [
                'label'          => 'mailvotech.core.date.added',
                'type'           => 'datetime',
                'groupByFormula' => 'DATE(l.date_added)',
            ],
            'l.points' => [
                'label' => 'mailvotech.lead.points',
                'type'  => 'int',
            ],
            'l.owner_id' => [
                'label' => 'mailvotech.lead.report.owner_id',
                'type'  => 'int',
            ],
            'u.first_name' => [
                'label' => 'mailvotech.lead.report.owner_firstname',
                'type'  => 'string',
            ],
            'u.last_name' => [
                'label' => 'mailvotech.lead.report.owner_lastname',
                'type'  => 'string',
            ],
            'l.generated_email_domain' => [
                'label' => 'mailvotech.lead.report.generated_email_domain',
                'type'  => 'string',
            ],
        ];
    }

    private function getBaseCompanyColumns(): array
    {
        return [
            'comp.id' => [
                'label' => 'mailvotech.lead.report.company.company_id',
                'type'  => 'int',
                'link'  => 'mailvotech_company_action',
            ],
            'comp.companyname' => [
                'label' => 'mailvotech.lead.report.company.company_name',
                'type'  => 'string',
                'link'  => 'mailvotech_company_action',
            ],
            'comp.companycity' => [
                'label' => 'mailvotech.lead.report.company.company_city',
                'type'  => 'string',
                'link'  => 'mailvotech_company_action',
            ],
            'comp.companystate' => [
                'label' => 'mailvotech.lead.report.company.company_state',
                'type'  => 'string',
                'link'  => 'mailvotech_company_action',
            ],
            'comp.companycountry' => [
                'label' => 'mailvotech.lead.report.company.company_country',
                'type'  => 'string',
                'link'  => 'mailvotech_company_action',
            ],
            'comp.companyindustry' => [
                'label' => 'mailvotech.lead.report.company.company_industry',
                'type'  => 'string',
                'link'  => 'mailvotech_company_action',
            ],
        ];
    }

    /**
     * @param LeadField[] $fields
     * @param string      $prefix
     */
    private function getFieldColumns($fields, $prefix): array
    {
        $prefix = $this->sanitizePrefix($prefix);

        $columns = [];
        foreach ($fields as $field) {
            $type = match ($field->getType()) {
                'boolean'  => 'bool',
                'date'     => 'date',
                'datetime' => 'datetime',
                'time'     => 'time',
                'url'      => 'url',
                'email'    => 'email',
                'number'   => 'float',
                default    => 'string',
            };
            $columns[$prefix.$field->getAlias()] = [
                'label' => $field->getLabel(),
                'type'  => $type,
            ];
        }

        return $columns;
    }

    /**
     * @param string $prefix
     */
    private function sanitizePrefix($prefix): string
    {
        if (!str_contains($prefix, '.')) {
            $prefix .= '.';
        }

        return $prefix;
    }
}
