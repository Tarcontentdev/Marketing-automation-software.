<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Field;

use MailVotech\CoreBundle\Helper\ArrayHelper;
use MailVotech\LeadBundle\Entity\CustomFieldEntityInterface;
use MailVotech\LeadBundle\Entity\LeadField;
use MailVotech\LeadBundle\Entity\LeadFieldRepository;
use MailVotech\LeadBundle\Field\DTO\CustomFieldFindReplaceCriteria;
use MailVotech\LeadBundle\Helper\CustomFieldHelper;

final readonly class CustomFieldFindReplace
{
    public function __construct(
        private FieldList $fieldList,
        private LeadFieldRepository $leadFieldRepository,
    ) {
    }

    /**
     * @return array<string,array<string,string>|string>
     */
    public function getFieldChoices(string $object): array
    {
        return ArrayHelper::flipArray(
            $this->fieldList->getFieldList(true, true, ['isPublished' => true, 'object' => $object])
        );
    }

    /**
     * @param iterable<object>                                                      $entities
     * @param callable(CustomFieldEntityInterface,array<string,mixed>): void        $setFieldValues
     * @param callable(CustomFieldEntityInterface): bool                            $canEdit
     * @param callable(CustomFieldEntityInterface): CustomFieldEntityInterface|null $refreshEntity
     *
     * @return array<int, CustomFieldEntityInterface>
     */
    public function replace(
        CustomFieldFindReplaceCriteria $criteria,
        iterable $entities,
        callable $setFieldValues,
        ?callable $canEdit = null,
        ?callable $refreshEntity = null,
    ): array {
        $field = $this->getPublishedFieldForObject($criteria->fieldAlias, $criteria->object);

        if (!$field instanceof LeadField) {
            return [];
        }

        $alias        = $field->getAlias();
        $fieldType    = $field->getType();
        $findValue    = $this->normalizeValue($fieldType, $criteria->findValue);
        $replaceValue = $this->normalizeValue($fieldType, $criteria->replaceValue);
        $updated      = [];

        if ($findValue === $replaceValue) {
            return [];
        }

        foreach ($entities as $entity) {
            if (!$entity instanceof CustomFieldEntityInterface) {
                continue;
            }

            if ($refreshEntity) {
                $entity = $refreshEntity($entity) ?? $entity;
            }

            if ($canEdit && !$canEdit($entity)) {
                continue;
            }

            if ($findValue !== $this->normalizeValue($fieldType, $entity->getFieldValue($alias))) {
                continue;
            }

            $setFieldValues($entity, [$alias => $replaceValue]);
            $updated[] = $entity;
        }

        return $updated;
    }

    private function getPublishedFieldForObject(string $alias, string $object): ?LeadField
    {
        $field = $this->leadFieldRepository->findOneBy([
            'alias'  => $alias,
            'object' => $object,
        ]);

        if (!$field instanceof LeadField || !$field->isPublished()) {
            return null;
        }

        return $field;
    }

    private function normalizeValue(string $fieldType, mixed $value): mixed
    {
        if (is_string($value)) {
            $value = trim($value);
        }

        if ('' === $value) {
            return null;
        }

        if (is_array($value)) {
            $value = implode('|', $value);
        }

        return CustomFieldHelper::fixValueType($fieldType, $value);
    }
}
