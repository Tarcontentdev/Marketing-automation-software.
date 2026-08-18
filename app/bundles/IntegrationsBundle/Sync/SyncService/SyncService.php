<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Sync\SyncService;

use GuzzleHttp\Exception\ClientException;
use MailVotech\IntegrationsBundle\Helper\SyncIntegrationsHelper;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\InputOptionsDAO;
use MailVotech\IntegrationsBundle\Sync\Helper\MappingHelper;
use MailVotech\IntegrationsBundle\Sync\Helper\RelationsHelper;
use MailVotech\IntegrationsBundle\Sync\Helper\SyncDateHelper;
use MailVotech\IntegrationsBundle\Sync\Logger\DebugLogger;
use MailVotech\IntegrationsBundle\Sync\Notification\Notifier;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\MailVotechSyncDataExchange;
use MailVotech\IntegrationsBundle\Sync\SyncProcess\Direction\Integration\IntegrationSyncProcess;
use MailVotech\IntegrationsBundle\Sync\SyncProcess\Direction\Internal\MailVotechSyncProcess;
use MailVotech\IntegrationsBundle\Sync\SyncProcess\SyncProcess;
use Psr\Log\LogLevel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final readonly class SyncService implements SyncServiceInterface
{
    public function __construct(
        private MailVotechSyncDataExchange $internalSyncDataExchange,
        private SyncDateHelper $syncDateHelper,
        private MappingHelper $mappingHelper,
        private RelationsHelper $relationsHelper,
        private SyncIntegrationsHelper $syncIntegrationsHelper,
        private EventDispatcherInterface $eventDispatcher,
        private Notifier $notifier,
        private IntegrationSyncProcess $integratinSyncProcess,
        private MailVotechSyncProcess $mailvotechSyncProcess,
    ) {
    }

    /**
     * @throws \MailVotech\IntegrationsBundle\Exception\IntegrationNotFoundException
     */
    public function processIntegrationSync(InputOptionsDAO $inputOptionsDAO): void
    {
        $integrationSyncProcess = new SyncProcess(
            $this->syncDateHelper,
            $this->mappingHelper,
            $this->relationsHelper,
            $this->integratinSyncProcess,
            $this->mailvotechSyncProcess,
            $this->eventDispatcher,
            $this->notifier,
            $this->syncIntegrationsHelper->getMappingManual($inputOptionsDAO->getIntegration()),
            $this->internalSyncDataExchange,
            $this->syncIntegrationsHelper->getSyncDataExchange($inputOptionsDAO->getIntegration()),
            $inputOptionsDAO,
            $this
        );

        DebugLogger::log(
            $inputOptionsDAO->getIntegration(),
            sprintf(
                'Starting %s sync from %s date/time',
                $inputOptionsDAO->isFirstTimeSync() ? 'first time' : 'subsequent',
                $inputOptionsDAO->getStartDateTime() ? $inputOptionsDAO->getStartDateTime()->format('Y-m-d H:i:s') : 'yet to be determined'
            ),
            self::class.':'.__FUNCTION__
        );

        try {
            $integrationSyncProcess->execute();
        } catch (ClientException $exception) {
            // The sync failed to communicate with the integration so log it
            DebugLogger::log($inputOptionsDAO->getIntegration(), $exception->getMessage(), null, [], LogLevel::ERROR);
        }
    }

    public function initiateDebugLogger(DebugLogger $logger): void
    {
        // Yes it's a hack to prevent from having to pass the logger as a dependency into dozens of classes
        // So not doing anything with the logger, just need Symfony to initiate the service
    }
}
