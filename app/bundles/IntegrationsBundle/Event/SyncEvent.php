<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Event;

use MailVotech\IntegrationsBundle\Sync\DAO\Sync\InputOptionsDAO;
use Symfony\Contracts\EventDispatcher\Event;

final class SyncEvent extends Event
{
    public function __construct(
        private readonly InputOptionsDAO $inputOptionsDAO,
    ) {
    }

    public function getIntegrationName(): string
    {
        return $this->inputOptionsDAO->getIntegration();
    }

    public function isIntegration(string $integrationName): bool
    {
        return $this->getIntegrationName() === $integrationName;
    }

    public function getFromDateTime(): ?\DateTimeInterface
    {
        return $this->inputOptionsDAO->getStartDateTime();
    }

    public function getToDateTime(): ?\DateTimeInterface
    {
        return $this->inputOptionsDAO->getEndDateTime();
    }

    public function getInputOptions(): InputOptionsDAO
    {
        return $this->inputOptionsDAO;
    }
}
