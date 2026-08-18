<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Field\Dispatcher;

use Doctrine\ORM\EntityManagerInterface;
use MailVotech\LeadBundle\Entity\LeadField;
use MailVotech\LeadBundle\Event\LeadFieldEvent;
use MailVotech\LeadBundle\Exception\NoListenerException;
use MailVotech\LeadBundle\LeadEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class FieldSaveDispatcher
{
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws NoListenerException
     */
    public function dispatchPreSaveEvent(LeadField $entity, bool $isNew): LeadFieldEvent
    {
        return $this->dispatchEvent(LeadEvents::FIELD_PRE_SAVE, $entity, $isNew);
    }

    /**
     * @throws NoListenerException
     */
    public function dispatchPostSaveEvent(LeadField $entity, bool $isNew): LeadFieldEvent
    {
        return $this->dispatchEvent(LeadEvents::FIELD_POST_SAVE, $entity, $isNew);
    }

    /**
     * @throws NoListenerException
     */
    public function dispatchEvent(string $action, LeadField $entity, bool $isNew, ?LeadFieldEvent $event = null): LeadFieldEvent
    {
        if (!$this->dispatcher->hasListeners($action)) {
            throw new NoListenerException('There is no Listener for '.$action.' event');
        }

        if (null === $event) {
            $event = new LeadFieldEvent($entity, $isNew);
            $event->setEntityManager($this->entityManager);
        }

        $this->dispatcher->dispatch($event, $action);

        return $event;
    }
}
