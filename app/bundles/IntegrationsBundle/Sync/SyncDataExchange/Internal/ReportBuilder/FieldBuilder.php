<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Internal\ReportBuilder;

use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Report\FieldDAO as ReportFieldDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Request\ObjectDAO as RequestObjectDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Value\NormalizedValueDAO;
use MailVotech\IntegrationsBundle\Sync\Exception\FieldNotFoundException;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Helper\FieldHelper;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Internal\ObjectHelper\ContactObjectHelper;
use MailVotech\IntegrationsBundle\Sync\ValueNormalizer\ValueNormalizer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class FieldBuilder
{
    private readonly ValueNormalizer $valueNormalizer;

    private ?array $mailvotechObject = null;

    private ?RequestObjectDAO $requestObject = null;

    public function __construct(
        private readonly RouterInterface $router,
        private readonly FieldHelper $fieldHelper,
        private readonly ContactObjectHelper $contactObjectHelper,
    ) {
        $this->valueNormalizer = new ValueNormalizer();
    }

    /**
     * @throws FieldNotFoundException
     */
    public function buildObjectField(
        string $field,
        array $mailvotechObject,
        RequestObjectDAO $requestObject,
        string $integration,
        string $defaultState = ReportFieldDAO::FIELD_CHANGED,
    ): ReportFieldDAO {
        $this->mailvotechObject  = $mailvotechObject;
        $this->requestObject = $requestObject;

        // Special handling of the ID field
        if ('mailvotech_internal_id' === $field) {
            return $this->addContactIdField($field);
        }

        // Special handling of the owner ID field
        if ('owner_id' === $field) {
            return $this->createOwnerIdReportFieldDAO($field, (int) $mailvotechObject['owner_id']);
        }

        // Special handling of DNC fields
        if (str_starts_with($field, 'mailvotech_internal_dnc_')) {
            return $this->addDoNotContactField($field);
        }

        // Special handling of timeline URL
        if ('mailvotech_internal_contact_timeline' === $field) {
            return $this->addContactTimelineField($integration, $field);
        }

        return $this->addCustomField($field, $defaultState);
    }

    private function addContactIdField(string $field): ReportFieldDAO
    {
        $normalizedValue = new NormalizedValueDAO(
            NormalizedValueDAO::INT_TYPE,
            $this->mailvotechObject['id']
        );

        return new ReportFieldDAO($field, $normalizedValue);
    }

    private function createOwnerIdReportFieldDAO(string $field, int $ownerId): ReportFieldDAO
    {
        return new ReportFieldDAO(
            $field,
            new NormalizedValueDAO(
                NormalizedValueDAO::INT_TYPE,
                $ownerId
            )
        );
    }

    private function addDoNotContactField(string $field): ReportFieldDAO
    {
        $channel = str_replace('mailvotech_internal_dnc_', '', $field);

        $normalizedValue = new NormalizedValueDAO(
            NormalizedValueDAO::INT_TYPE,
            $this->contactObjectHelper->getDoNotContactStatus((int) $this->mailvotechObject['id'], $channel)
        );

        return new ReportFieldDAO($field, $normalizedValue);
    }

    private function addContactTimelineField(string $integration, string $field): ReportFieldDAO
    {
        $normalizedValue = new NormalizedValueDAO(
            NormalizedValueDAO::URL_TYPE,
            $this->router->generate(
                'mailvotech_plugin_timeline_view',
                [
                    'integration' => $integration,
                    'leadId'      => $this->mailvotechObject['id'],
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );

        return new ReportFieldDAO($field, $normalizedValue);
    }

    /**
     * @throws FieldNotFoundException
     */
    private function addCustomField(string $field, string $defaultState): ReportFieldDAO
    {
        // The rest should be MailVotech custom fields and if not, just ignore
        $mailvotechFields = $this->fieldHelper->getFieldList($this->requestObject->getObject());
        if (!isset($mailvotechFields[$field])) {
            // Field must have been deleted or something so let's skip
            throw new FieldNotFoundException($field, $this->requestObject->getObject());
        }

        $requiredFields  = $this->requestObject->getRequiredFields();
        $fieldType       = $this->fieldHelper->getNormalizedFieldType($mailvotechFields[$field]['type']);
        $normalizedValue = $this->valueNormalizer->normalizeForMailVotech($fieldType, $this->mailvotechObject[$field]);

        return new ReportFieldDAO(
            $field,
            $normalizedValue,
            in_array($field, $requiredFields) ? ReportFieldDAO::FIELD_REQUIRED : $defaultState
        );
    }
}
