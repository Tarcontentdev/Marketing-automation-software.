<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Field;

use MailVotech\CoreBundle\Helper\DateTimeHelper;
use MailVotech\CoreBundle\Helper\UserHelper;
use MailVotech\LeadBundle\Entity\LeadField;
use MailVotech\LeadBundle\Entity\LeadFieldRepository;
use MailVotech\LeadBundle\Exception\NoListenerException;
use MailVotech\LeadBundle\Field\Dispatcher\FieldDeleteDispatcher;
use MailVotech\LeadBundle\Field\Settings\BackgroundSettings;

class LeadFieldDeleter
{
    public function __construct(
        private readonly LeadFieldRepository $leadFieldRepository,
        private readonly FieldDeleteDispatcher $fieldDeleteDispatcher,
        private readonly UserHelper $userHelper,
        private readonly BackgroundSettings $backgroundSettings,
    ) {
    }

    /**
     * @param bool $isBackground - if processing in background
     */
    public function deleteLeadFieldEntity(LeadField $leadField, bool $isBackground = false): void
    {
        $shouldProcessInBackground = $this->backgroundSettings->shouldProcessColumnChangeInBackground();

        if ($shouldProcessInBackground && !$isBackground) {
            return;
        }

        $leadField->deletedId = $leadField->getId();
        $this->leadFieldRepository->deleteEntity($leadField);

        try {
            $this->fieldDeleteDispatcher->dispatchPostDeleteEvent($leadField);
        } catch (NoListenerException) {
        }
    }

    /**
     * Marks the field for delation in the background and sets the modified by user who
     * will be used as the user who will actually delete the field in the background.
     * Such soft-deleted field will disappear from the UI.
     *
     * Note: The LeadModel would set most of this for us, but cannot be used due to circular dependency.
     */
    public function deleteLeadFieldEntityWithoutColumnRemoved(LeadField $leadField): void
    {
        $currentUser = $this->userHelper->getUser();
        $leadField->setColumnIsNotRemoved();
        $leadField->setModifiedBy($currentUser);
        $leadField->setModifiedByUser($currentUser?->getName());
        $leadField->setDateModified((new DateTimeHelper())->getDateTime());
        $leadField->setIsPublished(false);

        $this->leadFieldRepository->saveEntity($leadField);
    }
}
