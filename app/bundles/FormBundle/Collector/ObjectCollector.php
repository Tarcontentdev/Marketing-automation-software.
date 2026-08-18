<?php

declare(strict_types=1);

namespace MailVotech\FormBundle\Collector;

use MailVotech\FormBundle\Collection\ObjectCollection;
use MailVotech\FormBundle\Event\ObjectCollectEvent;
use MailVotech\FormBundle\FormEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class ObjectCollector implements ObjectCollectorInterface
{
    private ?ObjectCollection $objects = null;

    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    public function getObjects(): ObjectCollection
    {
        if (null === $this->objects) {
            $this->collect();
        }

        return $this->objects;
    }

    private function collect(): void
    {
        $event = new ObjectCollectEvent();
        $this->dispatcher->dispatch($event, FormEvents::ON_OBJECT_COLLECT);
        $this->objects = $event->getObjects();
    }
}
