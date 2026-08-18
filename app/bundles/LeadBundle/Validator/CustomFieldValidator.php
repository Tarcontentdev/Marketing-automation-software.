<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Validator;

use MailVotech\CoreBundle\Exception\InvalidValueException;
use MailVotech\CoreBundle\Exception\RecordNotFoundException;
use MailVotech\CoreBundle\Exception\RecordNotPublishedException;
use MailVotech\LeadBundle\Entity\LeadField;
use MailVotech\LeadBundle\Model\FieldModel;
use Symfony\Contracts\Translation\TranslatorInterface;

class CustomFieldValidator
{
    public function __construct(
        private readonly FieldModel $fieldModel,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * @throws RecordNotFoundException
     * @throws RecordNotPublishedException
     * @throws InvalidValueException
     */
    public function validateFieldType(string $alias, string $fieldType): void
    {
        $field = $this->getPublishedFieldByAlias($alias);

        if ($field->getType() !== $fieldType) {
            throw new InvalidValueException($this->translator->trans('mailvotech.lead.contact.wrong.field.type', ['%alias%' => $alias, '%fieldType%' => $field->getType(), '%expectedType%' => $fieldType], 'validators'));
        }
    }

    /**
     * @throws RecordNotFoundException
     * @throws RecordNotPublishedException
     */
    private function getPublishedFieldByAlias(string $alias): LeadField
    {
        $field = $this->getFieldByAlias($alias);

        if (!$field->getIsPublished()) {
            throw new RecordNotPublishedException($this->translator->trans('mailvotech.lead.contact.field.not.published', ['%alias%' => $alias], 'validators'));
        }

        return $field;
    }

    /**
     * @throws RecordNotFoundException
     */
    private function getFieldByAlias(string $alias): LeadField
    {
        $field = $this->fieldModel->getEntityByAlias($alias);

        if (!$field instanceof LeadField) {
            throw new RecordNotFoundException($this->translator->trans('mailvotech.lead.contact.field.not.found', ['%alias%' => $alias], 'validators'));
        }

        return $field;
    }
}
