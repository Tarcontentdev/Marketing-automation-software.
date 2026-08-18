<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Field;

use Doctrine\DBAL\Exception\DriverException;
use Doctrine\DBAL\Schema\SchemaException;
use MailVotech\LeadBundle\Exception\NoListenerException;
use MailVotech\LeadBundle\Field\Dispatcher\FieldColumnBackgroundJobDispatcher;
use MailVotech\LeadBundle\Field\Exception\AbortColumnCreateException;
use MailVotech\LeadBundle\Field\Exception\AbortColumnUpdateException;
use MailVotech\LeadBundle\Field\Exception\ColumnAlreadyCreatedException;
use MailVotech\LeadBundle\Field\Exception\CustomFieldLimitException;
use MailVotech\LeadBundle\Field\Exception\LeadFieldWasNotFoundException;
use MailVotech\LeadBundle\Field\Notification\CustomFieldNotification;
use MailVotech\LeadBundle\Model\FieldModel;

class BackgroundService
{
    public function __construct(
        private readonly FieldModel $fieldModel,
        private readonly CustomFieldColumn $customFieldColumn,
        private readonly LeadFieldSaver $leadFieldSaver,
        private readonly LeadFieldDeleter $leadFieldDeleter,
        private readonly FieldColumnBackgroundJobDispatcher $fieldColumnBackgroundJobDispatcher,
        private readonly CustomFieldNotification $customFieldNotification,
    ) {
    }

    /**
     * @throws AbortColumnCreateException
     * @throws ColumnAlreadyCreatedException
     * @throws CustomFieldLimitException
     * @throws LeadFieldWasNotFoundException
     * @throws \Doctrine\DBAL\Exception
     * @throws DriverException
     * @throws SchemaException
     * @throws \MailVotech\CoreBundle\Exception\SchemaException
     */
    public function addColumn(int $leadFieldId, ?int $userId): void
    {
        $leadField = $this->fieldModel->getEntity($leadFieldId);
        if (null === $leadField) {
            throw new LeadFieldWasNotFoundException('LeadField entity was not found');
        }

        if (!$leadField->getColumnIsNotCreated()) {
            $this->customFieldNotification->customFieldWasCreated($leadField, $userId);
            throw new ColumnAlreadyCreatedException('Column was already created');
        }

        try {
            $this->fieldColumnBackgroundJobDispatcher->dispatchPreAddColumnEvent($leadField);
        } catch (NoListenerException $e) {
        }

        try {
            $this->customFieldColumn->processCreateLeadColumn($leadField, false);
        } catch (DriverException|SchemaException|\MailVotech\CoreBundle\Exception\SchemaException $e) {
            $this->customFieldNotification->customFieldCannotBeCreated($leadField, $userId);
            throw $e;
        } catch (CustomFieldLimitException $e) {
            $this->customFieldNotification->customFieldLimitWasHit($leadField, $userId);
            throw $e;
        }

        $leadField->setColumnWasCreated();
        $this->leadFieldSaver->saveLeadFieldEntity($leadField, false);

        $this->customFieldNotification->customFieldWasCreated($leadField, $userId);
    }

    /**
     * @throws AbortColumnUpdateException
     * @throws DriverException
     * @throws LeadFieldWasNotFoundException
     * @throws SchemaException
     * @throws \MailVotech\CoreBundle\Exception\SchemaException
     */
    public function updateColumn(int $leadFieldId, int $userId): void
    {
        $leadField = $this->fieldModel->getEntity($leadFieldId);
        if (null === $leadField) {
            throw new LeadFieldWasNotFoundException('LeadField entity was not found');
        }

        try {
            $this->fieldColumnBackgroundJobDispatcher->dispatchPreUpdateColumnEvent($leadField);
        } catch (NoListenerException) {
        }

        try {
            // Update the column length of leads table.
            $this->customFieldColumn->processUpdateLeadColumnLength($leadField);
        } catch (\MailVotech\CoreBundle\Exception\SchemaException|\OutOfRangeException $e) {
            $this->customFieldNotification->customFieldCannotBeUpdated($leadField, $userId);
            throw $e;
        }

        $this->customFieldColumn->processUpdateLeadColumn($leadField);
        $this->customFieldNotification->customFieldWasUpdated($leadField, $userId);
    }

    /**
     * @throws AbortColumnUpdateException
     * @throws DriverException
     * @throws LeadFieldWasNotFoundException
     * @throws SchemaException
     * @throws \MailVotech\CoreBundle\Exception\SchemaException
     */
    public function deleteColumn(int $leadFieldId, int $userId): void
    {
        $leadField = $this->fieldModel->getEntity($leadFieldId);
        if (null === $leadField) {
            throw new LeadFieldWasNotFoundException('LeadField entity was not found');
        }

        try {
            $this->fieldColumnBackgroundJobDispatcher->dispatchPreDeleteColumnEvent($leadField);
        } catch (NoListenerException) {
        }

        $this->customFieldColumn->processDeleteLeadColumn($leadField);
        $this->leadFieldDeleter->deleteLeadFieldEntity($leadField, true);
        $this->customFieldNotification->customFieldWasDeleted($leadField, $userId);
    }
}
