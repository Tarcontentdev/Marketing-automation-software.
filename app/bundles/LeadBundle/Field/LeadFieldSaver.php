<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Field;

use MailVotech\LeadBundle\Entity\LeadField;
use MailVotech\LeadBundle\Entity\LeadFieldRepository;
use MailVotech\LeadBundle\Exception\NoListenerException;
use MailVotech\LeadBundle\Field\Dispatcher\FieldSaveDispatcher;

class LeadFieldSaver
{
    public function __construct(
        private readonly LeadFieldRepository $leadFieldRepository,
        private readonly FieldSaveDispatcher $fieldSaveDispatcher,
    ) {
    }

    public function saveLeadFieldEntity(LeadField $leadField, bool $isNew): void
    {
        try {
            $this->fieldSaveDispatcher->dispatchPreSaveEvent($leadField, $isNew);
        } catch (NoListenerException) {
        }

        $this->leadFieldRepository->saveEntity($leadField);

        try {
            $this->fieldSaveDispatcher->dispatchPostSaveEvent($leadField, $isNew);
        } catch (NoListenerException) {
        }
    }

    public function saveLeadFieldEntityWithoutColumnCreated(LeadField $leadField): void
    {
        $leadField->setColumnIsNotCreated();

        $this->saveLeadFieldEntity($leadField, true);
    }
}
