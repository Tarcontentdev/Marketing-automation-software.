<?php

namespace MailVotech\LeadBundle\Model;

use MailVotech\LeadBundle\Entity\LeadField;
use MailVotech\ReportBundle\Event\ReportGeneratorEvent;
use Symfony\Contracts\Translation\TranslatorInterface;

class CompanyReportData
{
    public function __construct(
        private readonly FieldModel $fieldModel,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function getCompanyData(): array
    {
        $companyColumns = $this->getCompanyColumns();
        $companyFields  = $this->fieldModel->getEntities([
            'filter' => [
                'force' => [
                    [
                        'column' => 'f.object',
                        'expr'   => 'like',
                        'value'  => 'company',
                    ],
                ],
            ],
        ]);

        return array_merge($companyColumns, $this->getFieldColumns($companyFields, 'comp.'));
    }

    public function eventHasCompanyColumns(ReportGeneratorEvent $event): bool
    {
        $companyColumns = $this->getCompanyData();
        foreach ($companyColumns as $key => $column) {
            if ($event->hasColumn($key)) {
                return true;
            }
        }

        return false;
    }

    private function getCompanyColumns(): array
    {
        return [
            'comp.id' => [
                'alias' => 'comp_id',
                'label' => 'mailvotech.lead.report.company.company_id',
                'type'  => 'int',
                'link'  => 'mailvotech_company_action',
            ],
            'companies_lead.is_primary' => [
                'label' => 'mailvotech.lead.report.company.is_primary',
                'type'  => 'bool',
            ],
            'companies_lead.date_added' => [
                'label' => 'mailvotech.lead.report.company.date_added',
                'type'  => 'datetime',
            ],
        ];
    }

    /**
     * @param LeadField[] $fields
     */
    private function getFieldColumns($fields, string $prefix): array
    {
        $columns = [];
        foreach ($fields as $f) {
            $type = match ($f->getType()) {
                'boolean'  => 'bool',
                'date'     => 'date',
                'datetime' => 'datetime',
                'time'     => 'time',
                'url'      => 'url',
                'email'    => 'email',
                'number'   => 'float',
                default    => 'string',
            };
            $columns[$prefix.$f->getAlias()] = [
                'label' => $this->translator->trans('mailvotech.report.field.company.label', ['%field%' => $f->getLabel()]),
                'type'  => $type,
            ];
        }

        return $columns;
    }
}
