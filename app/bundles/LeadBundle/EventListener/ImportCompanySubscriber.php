<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\EventListener;

use MailVotech\CoreBundle\Helper\ArrayHelper;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\LeadBundle\Event\ImportInitEvent;
use MailVotech\LeadBundle\Event\ImportMappingEvent;
use MailVotech\LeadBundle\Event\ImportProcessEvent;
use MailVotech\LeadBundle\Event\ImportValidateEvent;
use MailVotech\LeadBundle\Field\FieldList;
use MailVotech\LeadBundle\LeadEvents;
use MailVotech\LeadBundle\Model\CompanyModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class ImportCompanySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private FieldList $fieldList,
        private CorePermissions $corePermissions,
        private CompanyModel $companyModel,
        private TranslatorInterface $translator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LeadEvents::IMPORT_ON_INITIALIZE    => ['onImportInit'],
            LeadEvents::IMPORT_ON_FIELD_MAPPING => ['onFieldMapping'],
            LeadEvents::IMPORT_ON_PROCESS       => ['onImportProcess'],
            LeadEvents::IMPORT_ON_VALIDATE      => ['onValidateImport'],
        ];
    }

    /**
     * @throws AccessDeniedException
     */
    public function onImportInit(ImportInitEvent $event): void
    {
        if ($event->importIsForRouteObject('companies')) {
            if (!$this->corePermissions->isGranted('lead:imports:create')) {
                throw new AccessDeniedException('You do not have permission to import companies');
            }

            $event->objectSingular = 'company';
            $event->objectName     = 'mailvotech.lead.lead.companies';
            $event->activeLink     = '#mailvotech_company_index';
            $event->setIndexRoute('mailvotech_company_index');
            $event->stopPropagation();
        }
    }

    public function onFieldMapping(ImportMappingEvent $event): void
    {
        if ($event->importIsForRouteObject('companies')) {
            $specialFields = [
                'dateAdded'      => 'mailvotech.lead.import.label.dateAdded',
                'createdByUser'  => 'mailvotech.lead.import.label.createdByUser',
                'dateModified'   => 'mailvotech.lead.import.label.dateModified',
                'modifiedByUser' => 'mailvotech.lead.import.label.modifiedByUser',
            ];

            $event->fields = [
                'mailvotech.lead.company'        => $this->fieldList->getFieldList(false, false, ['isPublished' => true, 'object' => 'company']),
                'mailvotech.lead.special_fields' => $specialFields,
            ];
        }
    }

    public function onImportProcess(ImportProcessEvent $event): void
    {
        if ($event->importIsForObject('company')) {
            $merged = $this->companyModel->import(
                $event->import->getMatchedFields(),
                $event->rowData,
                $event->import->getDefault('owner'),
                (bool) $event->import->getDefault('skip_if_exists')
            );
            $event->setWasMerged($merged);
            $event->stopPropagation();
        }
    }

    public function onValidateImport(ImportValidateEvent $event): void
    {
        if (false === $event->importIsForRouteObject('companies')) {
            return;
        }

        $matchedFields = $event->getForm()->getData();
        $skipIfExists  = ArrayHelper::pickValue('skip_if_exists', $matchedFields, false);
        $event->setSkipIfExists((bool) $skipIfExists);
        unset($matchedFields['skip_if_exists']);
        $event->setOwnerId($this->handleValidateOwner($matchedFields));

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
            'object'      => 'company',
            'isRequired'  => true,
        ]);

        $missingRequiredFields = array_diff_key($requiredFields, array_flip($matchedFields));

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
