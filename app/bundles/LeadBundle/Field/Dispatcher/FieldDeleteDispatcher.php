<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Field\Dispatcher;

use Doctrine\ORM\EntityManagerInterface;
use MailVotech\LeadBundle\Entity\LeadField;
use MailVotech\LeadBundle\Event\LeadFieldEvent;
use MailVotech\LeadBundle\Exception\NoListenerException;
use MailVotech\LeadBundle\Field\Exception\AbortColumnUpdateException;
use MailVotech\LeadBundle\Field\Settings\BackgroundSettings;
use MailVotech\LeadBundle\LeadEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class FieldDeleteDispatcher
{
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly EntityManagerInterface $entityManager,
        private readonly BackgroundSettings $backgroundSettings,
    ) {
    }

    /**
     * @deprecated Use regular call of `$this->dispatchEvent(LeadEvents::FIELD_PRE_DELETE, $entity)` instead
     *
     * @throws NoListenerException
     * @throws AbortColumnUpdateException
     */
    public function dispatchPreDeleteEvent(LeadField $entity): LeadFieldEvent
    {
        if ($this->backgroundSettings->shouldProcessColumnChangeInBackground()) {
            throw new AbortColumnUpdateException('Column change will be processed in background job');
        }

        return $this->dispatchEvent(LeadEvents::FIELD_PRE_DELETE, $entity);
    }

    /**
     * @throws NoListenerException
     */
    public function dispatchPostDeleteEvent(LeadField $entity): LeadFieldEvent
    {
        return $this->dispatchEvent(LeadEvents::FIELD_POST_DELETE, $entity);
    }

    /**
     * @param string $action - Use constant from LeadEvents class (e.g. LeadEvents::FIELD_PRE_SAVE)
     *
     * @throws NoListenerException
     */
    private function dispatchEvent(string $action, LeadField $entity, ?LeadFieldEvent $event = null): LeadFieldEvent
    {
        if (!$this->dispatcher->hasListeners($action)) {
            throw new NoListenerException('There is no Listener for this event');
        }

        if (null === $event) {
            $event = new LeadFieldEvent($entity);
            $event->setEntityManager($this->entityManager);
        }

        $this->dispatcher->dispatch($event, $action);

        return $event;
    }
}
