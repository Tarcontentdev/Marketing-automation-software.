<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Internal\ReportBuilder;

use MailVotech\IntegrationsBundle\Event\InternalCompanyEvent;
use MailVotech\IntegrationsBundle\Event\InternalContactEvent;
use MailVotech\IntegrationsBundle\Event\InternalObjectFindByIdEvent;
use MailVotech\IntegrationsBundle\Event\InternalObjectFindEvent;
use MailVotech\IntegrationsBundle\Exception\InvalidValueException;
use MailVotech\IntegrationsBundle\IntegrationEvents;
use MailVotech\IntegrationsBundle\Sync\DAO\DateRange;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Report\ObjectDAO as ReportObjectDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Report\ReportDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Request\ObjectDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Request\RequestDAO;
use MailVotech\IntegrationsBundle\Sync\Exception\FieldNotFoundException;
use MailVotech\IntegrationsBundle\Sync\Exception\ObjectNotFoundException;
use MailVotech\IntegrationsBundle\Sync\Logger\DebugLogger;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Internal\ObjectProvider;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\MailVotechSyncDataExchange;
use MailVotech\LeadBundle\Entity\Company;
use MailVotech\LeadBundle\Entity\Lead;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class FullObjectReportBuilder
{
    public function __construct(
        private readonly FieldBuilder $fieldBuilder,
        private readonly ObjectProvider $objectProvider,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    public function buildReport(RequestDAO $requestDAO): ReportDAO
    {
        $syncReport       = new ReportDAO($requestDAO->getSyncToIntegration());
        $requestedObjects = $requestDAO->getObjects();
        $limit            = 200;
        $start            = $limit * ($requestDAO->getSyncIteration() - 1);

        foreach ($requestedObjects as $requestedObjectDAO) {
            try {
                DebugLogger::log(
                    $requestDAO->getSyncToIntegration(),
                    sprintf(
                        'Searching for %s objects between %s and %s (%d,%d)',
                        $requestedObjectDAO->getObject(),
                        $requestedObjectDAO->getFromDateTime()->format(DATE_ATOM),
                        $requestedObjectDAO->getToDateTime()->format(DATE_ATOM),
                        $start,
                        $limit
                    ),
                    self::class.':'.__FUNCTION__
                );

                $event = new InternalObjectFindEvent(
                    $this->objectProvider->getObjectByName($requestedObjectDAO->getObject())
                );

                if ($requestDAO->getInputOptionsDAO()->getMailVotechObjectIds()) {
                    $idChunks = array_chunk($requestDAO->getInputOptionsDAO()->getMailVotechObjectIds()->getObjectIdsFor($requestedObjectDAO->getObject()), $limit);
                    $idChunk  = $idChunks[$requestDAO->getSyncIteration() - 1] ?? [];
                    $event->setIds($idChunk);
                } else {
                    $event->setDateRange(
                        new DateRange(
                            $requestedObjectDAO->getFromDateTime(),
                            $requestedObjectDAO->getToDateTime()
                        )
                    );
                    $event->setStart($start);
                    $event->setLimit($limit);
                }

                $this->dispatcher->dispatch(
                    $event,
                    IntegrationEvents::INTEGRATION_FIND_INTERNAL_RECORDS
                );

                $foundObjects = $event->getFoundObjects();

                $this->processObjects($requestedObjectDAO, $syncReport, $foundObjects);
            } catch (ObjectNotFoundException $exception) {
                DebugLogger::log(
                    MailVotechSyncDataExchange::NAME,
                    $exception->getMessage(),
                    self::class.':'.__FUNCTION__
                );
            }
        }

        return $syncReport;
    }

    /**
     * @throws ObjectNotFoundException
     */
    private function processObjects(ObjectDAO $requestedObjectDAO, ReportDAO $syncReport, array $foundObjects): void
    {
        $fields = $requestedObjectDAO->getFields();

        if ($this->dispatcher->hasListeners(IntegrationEvents::INTEGRATION_FIND_INTERNAL_RECORD)) {
            $event = new InternalObjectFindByIdEvent($this->objectProvider->getObjectByName($requestedObjectDAO->getObject()));
        }

        foreach ($foundObjects as $object) {
            $modifiedDateTime = new \DateTime(
                !empty($object['date_modified']) ? $object['date_modified'] : $object['date_added'],
                new \DateTimeZone('UTC')
            );
            $reportObjectDAO = new ReportObjectDAO($requestedObjectDAO->getObject(), $object['id'], $modifiedDateTime);
            $syncReport->addObject($reportObjectDAO);

            if (isset($event)) {
                // Update object id rather than creating the event again.
                $event->setId((int) $object['id']);
                $this->dispatcher->dispatch($event, IntegrationEvents::INTEGRATION_FIND_INTERNAL_RECORD);

                if (!$event->getEntity()) {
                    // Object not found, continue.
                    continue;
                }

                try {
                    $this->dispatchBeforeFieldChangesEvent($syncReport->getIntegration(), $event->getEntity());
                } catch (InvalidValueException) {
                    // Object is not eligible, continue.
                    continue;
                }
            }

            foreach ($fields as $field) {
                try {
                    $reportFieldDAO = $this->fieldBuilder->buildObjectField($field, $object, $requestedObjectDAO, $syncReport->getIntegration());
                    $reportObjectDAO->addField($reportFieldDAO);
                } catch (FieldNotFoundException $exception) {
                    // Field is not supported so keep going
                    DebugLogger::log(
                        MailVotechSyncDataExchange::NAME,
                        $exception->getMessage(),
                        self::class.':'.__FUNCTION__
                    );
                }
            }
        }
    }

    /**
     * @throws InvalidValueException
     */
    private function dispatchBeforeFieldChangesEvent(string $integrationName, object $object): void
    {
        if ($object instanceof Lead) {
            if ($this->dispatcher->hasListeners(IntegrationEvents::INTEGRATION_BEFORE_FULL_CONTACT_REPORT_BUILD)) {
                $this->dispatcher->dispatch(
                    new InternalContactEvent($integrationName, $object),
                    IntegrationEvents::INTEGRATION_BEFORE_FULL_CONTACT_REPORT_BUILD
                );
            }

            return;
        }

        if ($object instanceof Company) {
            if ($this->dispatcher->hasListeners(IntegrationEvents::INTEGRATION_BEFORE_FULL_COMPANY_REPORT_BUILD)) {
                $this->dispatcher->dispatch(
                    new InternalCompanyEvent($integrationName, $object),
                    IntegrationEvents::INTEGRATION_BEFORE_FULL_COMPANY_REPORT_BUILD
                );
            }

            return;
        }

        throw new InvalidValueException('An object type should be specified. None matches.');
    }
}
