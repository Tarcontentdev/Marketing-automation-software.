<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Event;

use MailVotech\IntegrationsBundle\Sync\DAO\Mapping\MappingManualDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\InputOptionsDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Order\OrderResultsDAO;
use Symfony\Contracts\EventDispatcher\Event;

final class CompletedSyncIterationEvent extends Event
{
    public function __construct(
        private readonly OrderResultsDAO $orderResultsDAO,
        private readonly int $iteration,
        private readonly InputOptionsDAO $inputOptionsDAO,
        private readonly MappingManualDAO $mappingManualDAO,
    ) {
    }

    public function getIntegration(): string
    {
        return $this->mappingManualDAO->getIntegration();
    }

    public function getOrderResults(): OrderResultsDAO
    {
        return $this->orderResultsDAO;
    }

    public function getIteration(): int
    {
        return $this->iteration;
    }

    public function getInputOptions(): InputOptionsDAO
    {
        return $this->inputOptionsDAO;
    }

    public function getMappingManual(): MappingManualDAO
    {
        return $this->mappingManualDAO;
    }
}
