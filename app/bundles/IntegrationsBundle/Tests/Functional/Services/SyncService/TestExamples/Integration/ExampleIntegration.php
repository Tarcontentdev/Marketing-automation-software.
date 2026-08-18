<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Tests\Functional\Services\SyncService\TestExamples\Integration;

use MailVotech\IntegrationsBundle\Integration\BasicIntegration;
use MailVotech\IntegrationsBundle\Integration\Interfaces\IntegrationInterface;
use MailVotech\IntegrationsBundle\Integration\Interfaces\SyncInterface;
use MailVotech\IntegrationsBundle\Sync\DAO\Mapping\MappingManualDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Mapping\ObjectMappingDAO;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Internal\Object\Contact;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\SyncDataExchangeInterface;
use MailVotech\IntegrationsBundle\Tests\Functional\Services\SyncService\TestExamples\Sync\SyncDataExchange\ExampleSyncDataExchange;

final class ExampleIntegration extends BasicIntegration implements IntegrationInterface, SyncInterface
{
    public const NAME = 'Example';

    public function __construct(
        private readonly ExampleSyncDataExchange $syncDataExchange,
    ) {
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function isConfigured(): bool
    {
        return true;
    }

    public function isAuthorized(): bool
    {
        return true;
    }

    /**
     * Get if data priority is enabled in the integration or not default is false.
     */
    public function getDataPriority(): bool
    {
        return true;
    }

    public function getSyncDataExchange(): SyncDataExchangeInterface
    {
        return $this->syncDataExchange;
    }

    public function getMappingManual(): MappingManualDAO
    {
        // Generate mapping manual that will be passed to the sync service. This instructs the sync service how to map MailVotech fields to integration fields
        $mappingManual = new MappingManualDAO(self::NAME);

        // Each object like lead, contact, user, company, account, etc, will need it's own ObjectMappingDAO
        // In this example, MailVotech's Contact object is mapped to the Example's Lead object
        $leadObjectMapping = new ObjectMappingDAO(
            Contact::NAME,
            ExampleSyncDataExchange::OBJECT_LEAD
        );
        $mappingManual->addObjectMapping($leadObjectMapping);

        // Get field mapping as configured in MailVotech's integration config
        $mappedFields = $this->getConfiguredFieldMapping();

        foreach ($mappedFields as $integrationField => $mailvotechField) {
            // In this case, we're just adding each field to each of the objects
            // Of course, other integrations may need more logic

            // Sync bidirectionally by default but also can use ObjectMappingDAO::SYNC_TO_MAILVOTECH or ObjectMappingDAO::SYNC_TO_INTEGRATION

            if ('email' === $mailvotechField) {
                // Set email as a required field so that it maps a value regardless if changed
                $leadObjectMapping->addFieldMapping($mailvotechField, $integrationField, ObjectMappingDAO::SYNC_BIDIRECTIONALLY, true);
            } else {
                $leadObjectMapping->addFieldMapping($mailvotechField, $integrationField);
            }
        }

        return $mappingManual;
    }

    /**
     * Likely will get this mapping out of the Integration's settings.
     */
    private function getConfiguredFieldMapping(): array
    {
        return [
            'first_name' => 'firstname',
            'last_name'  => 'lastname',
            'email'      => 'email',
            'street1'    => 'address1',
        ];
    }
}
