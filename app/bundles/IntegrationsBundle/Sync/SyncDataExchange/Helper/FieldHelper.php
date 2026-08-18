<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Helper;

use MailVotech\ChannelBundle\Helper\ChannelListHelper;
use MailVotech\IntegrationsBundle\Event\MailVotechSyncFieldsLoadEvent;
use MailVotech\IntegrationsBundle\IntegrationEvents;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Report\FieldDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Value\EncodedValueDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Value\NormalizedValueDAO;
use MailVotech\IntegrationsBundle\Sync\Exception\ObjectNotFoundException;
use MailVotech\IntegrationsBundle\Sync\Exception\ObjectNotSupportedException;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Internal\Object\Contact;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Internal\ObjectProvider;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\MailVotechSyncDataExchange;
use MailVotech\IntegrationsBundle\Sync\VariableExpresser\VariableExpresserHelperInterface;
use MailVotech\LeadBundle\Field\FieldsWithUniqueIdentifier;
use MailVotech\LeadBundle\Model\FieldModel;
use MailVotech\LeadBundle\Model\LeadModel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class FieldHelper
{
    private array $fieldList = [];

    private array $requiredFieldList = [];

    private array $syncFields = [];

    public function __construct(
        private readonly FieldModel $fieldModel,
        private readonly FieldsWithUniqueIdentifier $fieldWithUniqueIdentifier,
        private readonly VariableExpresserHelperInterface $variableExpresserHelper,
        private readonly ChannelListHelper $channelListHelper,
        private readonly TranslatorInterface $translator,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ObjectProvider $objectProvider,
    ) {
    }

    public function getFieldList(string $object): array
    {
        if (!isset($this->fieldList[$object])) {
            $this->fieldList[$object] = $this->fieldModel->getFieldListWithProperties($object);
        }

        return $this->fieldList[$object];
    }

    public function getNormalizedFieldType(string $type): string
    {
        return match ($type) {
            'boolean' => NormalizedValueDAO::BOOLEAN_TYPE,
            'date', 'datetime', 'time' => NormalizedValueDAO::DATETIME_TYPE,
            'number'      => NormalizedValueDAO::FLOAT_TYPE,
            'select'      => NormalizedValueDAO::SELECT_TYPE,
            'multiselect' => NormalizedValueDAO::MULTISELECT_TYPE,
            default       => NormalizedValueDAO::STRING_TYPE,
        };
    }

    /**
     * @throws ObjectNotSupportedException
     */
    public function getFieldObjectName(string $objectName): string
    {
        try {
            return $this->objectProvider->getObjectByName($objectName)->getEntityName();
        } catch (ObjectNotFoundException) {
            // Throwing different exception to keep BC.
            throw new ObjectNotSupportedException(MailVotechSyncDataExchange::NAME, $objectName);
        }
    }

    public function getFieldChangeObject(array $fieldChange): FieldDAO
    {
        $changeTimestamp = new \DateTimeImmutable($fieldChange['modified_at'], new \DateTimeZone('UTC'));
        $columnType      = $fieldChange['column_type'];
        $columnValue     = $fieldChange['column_value'];
        $newValue        = $this->variableExpresserHelper->decodeVariable(new EncodedValueDAO($columnType, $columnValue));

        $reportFieldDAO = new FieldDAO($fieldChange['column_name'], $newValue);
        $reportFieldDAO->setChangeDateTime($changeTimestamp);

        return $reportFieldDAO;
    }

    public function getSyncFields(string $objectName): array
    {
        if (isset($this->syncFields[$objectName])) {
            return $this->syncFields[$objectName];
        }

        $this->syncFields[$objectName] = $this->fieldModel->getFieldList(
            false,
            true,
            [
                'isPublished' => true,
                'object'      => $objectName,
            ]
        );

        // Dispatch event to add possibility to add field from some listener
        $event                                     = new MailVotechSyncFieldsLoadEvent($objectName, $this->syncFields[$objectName]);
        $event                                     = $this->eventDispatcher->dispatch($event, IntegrationEvents::INTEGRATION_MAILVOTECH_SYNC_FIELDS_LOAD);
        $this->syncFields[$event->getObjectName()] = $event->getFields();

        // Add ID as a read only field
        $this->syncFields[$objectName]['mailvotech_internal_id'] = $this->translator->trans('mailvotech.core.id');

        if (Contact::NAME !== $objectName) {
            uksort($this->syncFields[$objectName], strnatcmp(...));

            return $this->syncFields[$objectName];
        }

        // MailVotech contacts have "pseudo" fields such as channel do not contact, timeline, etc.
        $channels = $this->channelListHelper->getFeatureChannels([LeadModel::CHANNEL_FEATURE], true);
        foreach ($channels as $label => $channel) {
            $this->syncFields[$objectName]['mailvotech_internal_dnc_'.$channel] = $this->translator->trans('mailvotech.integration.sync.channel_dnc', ['%channel%' => $label]);
        }

        // Add the timeline link
        $this->syncFields[$objectName]['mailvotech_internal_contact_timeline'] = $this->translator->trans('mailvotech.integration.sync.contact_timeline');

        uksort($this->syncFields[$objectName], strnatcmp(...));

        return $this->syncFields[$objectName];
    }

    /**
     * @return mixed[]
     */
    public function getRequiredFields(string $object): array
    {
        if (isset($this->requiredFieldList[$object])) {
            return $this->requiredFieldList[$object];
        }

        $requiredFields = $this->fieldModel->getFieldList(
            false,
            false,
            [
                'isPublished' => true,
                'isRequired'  => true,
                'object'      => $object,
            ]
        );

        // We don't use unique identifier field for companies.
        if ('company' === $object) {
            $this->requiredFieldList[$object] = $requiredFields;

            return $this->requiredFieldList[$object];
        }

        $uniqueIdentifierFields = $this->fieldWithUniqueIdentifier->getFieldsWithUniqueIdentifier(
            [
                'isPublished' => true,
                'object'      => $object,
            ]
        );

        $this->requiredFieldList[$object] = array_merge($requiredFields, $uniqueIdentifierFields);

        return $this->requiredFieldList[$object];
    }
}
