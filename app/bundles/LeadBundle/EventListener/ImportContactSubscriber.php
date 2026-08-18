<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\EventListener;

use Doctrine\Common\Collections\ArrayCollection;
use MailVotech\CoreBundle\Helper\ArrayHelper;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\LeadBundle\Entity\Tag;
use MailVotech\LeadBundle\Event\ImportInitEvent;
use MailVotech\LeadBundle\Event\ImportMappingEvent;
use MailVotech\LeadBundle\Event\ImportProcessEvent;
use MailVotech\LeadBundle\Event\ImportValidateEvent;
use MailVotech\LeadBundle\Field\FieldList;
use MailVotech\LeadBundle\LeadEvents;
use MailVotech\LeadBundle\Model\LeadModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class ImportContactSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private FieldList $fieldList,
        private CorePermissions $corePermissions,
        private LeadModel $contactModel,
        private TranslatorInterface $translator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LeadEvents::IMPORT_ON_INITIALIZE    => 'onImportInit',
            LeadEvents::IMPORT_ON_FIELD_MAPPING => 'onFieldMapping',
            LeadEvents::IMPORT_ON_PROCESS       => 'onImportProcess',
            LeadEvents::IMPORT_ON_VALIDATE      => 'onValidateImport',
        ];
    }

    /**
     * @throws AccessDeniedException
     */
    public function onImportInit(ImportInitEvent $event): void
    {
        if ($event->importIsForRouteObject('contacts')) {
            if (!$this->corePermissions->isGranted('lead:imports:create')) {
                throw new AccessDeniedException('You do not have permission to import contacts');
            }

            $event->objectSingular = 'lead';
            $event->objectName     = 'mailvotech.lead.leads';
            $event->activeLink     = '#mailvotech_contact_index';
            $event->setIndexRoute('mailvotech_contact_index');
            $event->stopPropagation();
        }
    }

    public function onFieldMapping(ImportMappingEvent $event): void
    {
        if ($event->importIsForRouteObject('contacts')) {
            $specialFields = [
                'dateAdded'      => 'mailvotech.lead.import.label.dateAdded',
                'createdByUser'  => 'mailvotech.lead.import.label.createdByUser',
                'dateModified'   => 'mailvotech.lead.import.label.dateModified',
                'modifiedByUser' => 'mailvotech.lead.import.label.modifiedByUser',
                'lastActive'     => 'mailvotech.lead.import.label.lastActive',
                'dateIdentified' => 'mailvotech.lead.import.label.dateIdentified',
                'ip'             => 'mailvotech.lead.import.label.ip',
                'stage'          => 'mailvotech.lead.import.label.stage',
                'doNotEmail'     => 'mailvotech.lead.import.label.doNotEmail',
                'ownerusername'  => 'mailvotech.lead.import.label.ownerusername',
                'tags'           => 'mailvotech.lead.import.label.tags',
            ];

            // Add ID to lead fields to allow matching import contacts by identifier
            $contactFields = array_merge(['id' => 'mailvotech.lead.import.label.id'], $this->fieldList->getFieldList(false, false));

            $event->fields = [
                'mailvotech.lead.contact'        => $contactFields,
                'mailvotech.lead.company'        => $this->fieldList->getFieldList(false, false, ['isPublished' => true, 'object' => 'company']),
                'mailvotech.lead.special_fields' => $specialFields,
            ];
        }
    }

    public function onImportProcess(ImportProcessEvent $event): void
    {
        if ($event->importIsForObject('lead')) {
            $merged = $this->contactModel->import(
                $event->import->getMatchedFields(),
                $event->rowData,
                $event->import->getDefault('owner'),
                $event->import->getDefault('list'),
                $event->import->getDefault('tags'),
                true,
                $event->eventLog,
                (int) $event->import->getId(),
                (bool) $event->import->getDefault('skip_if_exists')
            );
            $event->setWasMerged($merged);
            $event->stopPropagation();
        }
    }

    public function onValidateImport(ImportValidateEvent $event): void
    {
        if (false === $event->importIsForRouteObject('contacts')) {
            return;
        }

        $matchedFields = $event->getForm()->getData();

        $skipIfExists = ArrayHelper::pickValue('skip_if_exists', $matchedFields, false);
        $event->setSkipIfExists((bool) $skipIfExists);
        $event->setOwnerId($this->handleValidateOwner($matchedFields));
        $event->setList($this->handleValidateList($matchedFields));
        $event->setTags($this->handleValidateTags($matchedFields));

        $matchedFields = array_map(
            fn ($value) => is_string($value) ? trim($value) : $value,
            array_filter($matchedFields)
        );

        if ([] === $matchedFields) {
            $event->getForm()->addError(
                new FormError(
                    $this->translator->trans('mailvotech.lead.import.matchfields', [], 'validators')
                )
            );
        }

        $this->handleValidateRequired($event, $matchedFields);

        $event->setMatchedFields($matchedFields);
    }

    /**
     * @param mixed[] $matchedFields
     */
    private function handleValidateOwner(array &$matchedFields): ?int
    {
        $owner = ArrayHelper::pickValue('owner', $matchedFields);

        return $owner ? $owner->getId() : null;
    }

    /**
     * @param mixed[] $matchedFields
     */
    private function handleValidateList(array &$matchedFields): ?int
    {
        return ArrayHelper::pickValue('list', $matchedFields);
    }

    /**
     * @param mixed[] $matchedFields
     *
     * @return mixed[]
     */
    private function handleValidateTags(array &$matchedFields): array
    {
        // In case $matchedFields['tags'] === null ...
        $tags = ArrayHelper::pickValue('tags', $matchedFields, []);
        // ...we must ensure we pass an [] to array_map
        $tags = $tags instanceof ArrayCollection ? $tags->toArray() : [];

        return array_map(fn (Tag $tag) => $tag->getTag(), $tags);
    }

    /**
     * Validate required fields.
     *
     * Required fields come through as ['alias' => 'label'], and
     * $matchedFields is a zero indexed array, so to calculate the
     * diff, we must array_flip($matchedFields) and compare on key.
     *
     * @param mixed[] $matchedFields
     */
    private function handleValidateRequired(ImportValidateEvent $event, array &$matchedFields): void
    {
        $requiredFields = $this->fieldList->getFieldList(false, false, [
            'isPublished' => true,
            'object'      => 'lead',
            'isRequired'  => true,
        ]);

        $missingRequiredFields = array_diff_key($requiredFields, array_flip($matchedFields));

        // Check for the presense of company mapped fields
        $companyFields = array_filter($matchedFields, fn ($fieldname): bool => is_string($fieldname) && str_starts_with($fieldname, 'company'));

        // If we have any, ensure all required company fields are mapped.
        if (count($companyFields)) {
            $companyRequiredFields = $this->fieldList->getFieldList(false, false, [
                'isPublished' => true,
                'object'      => 'company',
                'isRequired'  => true,
            ]);

            $companyMissingRequiredFields = array_diff_key($companyRequiredFields, array_flip($matchedFields));

            if (count($companyMissingRequiredFields)) {
                $missingRequiredFields = array_merge($missingRequiredFields, $companyMissingRequiredFields);
            }
        }

        if (count($missingRequiredFields)) {
            $event->getForm()->addError(
                new FormError(
                    $this->translator->trans(
                        'mailvotech.import.missing.required.fields',
                        [
                            '%requiredFields%' => implode(', ', $missingRequiredFields),
                            '%fieldOrFields%'  => 1 === count($missingRequiredFields) ? 'field' : 'fields',
                        ],
                        'validators'
                    )
                )
            );
        }
    }
}
