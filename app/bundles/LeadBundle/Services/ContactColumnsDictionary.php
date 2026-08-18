<?php

namespace MailVotech\LeadBundle\Services;

use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\LeadBundle\Model\FieldModel;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ContactColumnsDictionary
{
    /**
     * @var mixed[]
     */
    private array $fieldList = [];

    public function __construct(
        private readonly FieldModel $fieldModel,
        private readonly TranslatorInterface $translator,
        private readonly CoreParametersHelper $coreParametersHelper,
    ) {
    }

    public function getColumns(): array
    {
        $columns = array_flip($this->coreParametersHelper->get('contact_columns', []));
        $fields  = $this->getFields();
        foreach ($columns as $alias=>&$column) {
            if (isset($fields[$alias])) {
                $column = $fields[$alias];
            }
        }

        return $columns;
    }

    public function getFields(): array
    {
        if ([] === $this->fieldList) {
            $this->fieldList['name']        = sprintf(
                '%s %s',
                $this->translator->trans('mailvotech.core.firstname'),
                $this->translator->trans('mailvotech.core.lastname')
            );
            $this->fieldList['email']       = $this->translator->trans('mailvotech.core.type.email');
            $this->fieldList['location']    = $this->translator->trans('mailvotech.lead.lead.thead.location');
            $this->fieldList['stage']       = $this->translator->trans('mailvotech.lead.stage.label');
            $this->fieldList['points']      = $this->translator->trans('mailvotech.lead.points');
            $this->fieldList['last_active'] = $this->translator->trans('mailvotech.lead.lastactive');
            $this->fieldList['id']          = $this->translator->trans('mailvotech.core.id');
            $this->fieldList += $this->fieldModel->getFieldList(false);
        }

        return $this->fieldList;
    }
}
