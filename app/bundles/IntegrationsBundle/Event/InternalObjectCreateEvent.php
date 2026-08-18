<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Event;

use MailVotech\IntegrationsBundle\Entity\ObjectMapping;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Internal\Object\ObjectInterface;
use Symfony\Contracts\EventDispatcher\Event;

final class InternalObjectCreateEvent extends Event
{
    /**
     * @var ObjectMapping[]
     */
    private array $objectMappings = [];

    public function __construct(
        private readonly ObjectInterface $object,
        private readonly array $createObjects,
    ) {
    }

    public function getObject(): ObjectInterface
    {
        return $this->object;
    }

    public function getCreateObjects(): array
    {
        return $this->createObjects;
    }

    /**
     * @return ObjectMapping[]
     */
    public function getObjectMappings(): array
    {
        return $this->objectMappings;
    }

    /**
     * @param ObjectMapping[] $objectMappings
     */
    public function setObjectMappings(array $objectMappings): void
    {
        $this->objectMappings = $objectMappings;
    }
}
