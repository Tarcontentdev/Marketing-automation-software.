<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Event;

use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Internal\Object\ObjectInterface;
use Symfony\Contracts\EventDispatcher\Event;

final class InternalObjectEvent extends Event
{
    private array $objects = [];

    public function addObject(ObjectInterface $object): void
    {
        $this->objects[] = $object;
    }

    /**
     * @return ObjectInterface[]
     */
    public function getObjects(): array
    {
        return $this->objects;
    }
}
