<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Services;

use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\LeadBundle\Field\FieldList;
use Symfony\Contracts\Translation\TranslatorInterface;

final class CompanyColumnsDictionary
{
    /**
     * @var array<string, string>
     */
    private array $cachedChoices = [];

    public function __construct(
        private readonly FieldList $fieldList,
        private readonly TranslatorInterface $translator,
        private readonly CoreParametersHelper $coreParametersHelper,
    ) {
    }

    /**
     * @return array<string, string> ordered column alias => label
     */
    public function getColumns(): array
    {
        $rawColumns = $this->coreParametersHelper->get('company_columns', []);
        if (!\is_array($rawColumns)) {
            $rawColumns = [];
        }

        $columns = array_flip($rawColumns);
        $fields  = $this->getFields();

        foreach ($columns as $alias => &$column) {
            if (isset($fields[(string) $alias])) {
                $column = $fields[(string) $alias];
            }
        }

        return $columns;
    }

    /**
     * @return array<string, string> alias => label available for choices
     */
    public function getFields(): array
    {
        if ([] === $this->cachedChoices) {
            $this->cachedChoices = [
                'companyname'    => $this->translator->trans('mailvotech.company.name'),
                'companyemail'   => $this->translator->trans('mailvotech.company.email'),
                'companywebsite' => $this->translator->trans('mailvotech.company.website'),
                'score'          => $this->translator->trans('mailvotech.company.score'),
                'leadcount'      => $this->translator->trans('mailvotech.lead.list.thead.leadcount'),
                'id'             => $this->translator->trans('mailvotech.core.id'),
            ];

            $this->cachedChoices += $this->fieldList->getFieldList(
                false,
                true,
                ['isPublished' => true, 'object' => 'company']
            );
        }

        return $this->cachedChoices;
    }
}
