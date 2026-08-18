<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Field\Dispatcher;

use MailVotech\LeadBundle\Entity\LeadField;
use MailVotech\LeadBundle\Exception\NoListenerException;
use MailVotech\LeadBundle\Field\Event\AddColumnBackgroundEvent;
use MailVotech\LeadBundle\Field\Event\DeleteColumnBackgroundEvent;
use MailVotech\LeadBundle\Field\Event\UpdateColumnBackgroundEvent;
use MailVotech\LeadBundle\Field\Exception\AbortColumnCreateException;
use MailVotech\LeadBundle\Field\Exception\AbortColumnUpdateException;
use MailVotech\LeadBundle\LeadEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class FieldColumnBackgroundJobDispatcher
{
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    /**
     * @throws AbortColumnCreateException
     * @throws NoListenerException
     */
    public function dispatchPreAddColumnEvent(LeadField $leadField): void
    {
        $action = LeadEvents::LEAD_FIELD_PRE_ADD_COLUMN_BACKGROUND_JOB;

        if (!$this->dispatcher->hasListeners($action)) {
            throw new NoListenerException('There is no Listener for this event');
        }

        $event = new AddColumnBackgroundEvent($leadField);

        $this->dispatcher->dispatch($event, $action);

        if ($event->isPropagationStopped()) {
            throw new AbortColumnCreateException('Column cannot be created now');
        }
    }

    /**
     * @throws AbortColumnUpdateException
     * @throws NoListenerException
     */
    public function dispatchPreUpdateColumnEvent(LeadField $leadField): void
    {
        $action = LeadEvents::LEAD_FIELD_PRE_UPDATE_COLUMN_BACKGROUND_JOB;

        if (!$this->dispatcher->hasListeners($action)) {
            throw new NoListenerException('There is no Listener for this event');
        }

        $event = new UpdateColumnBackgroundEvent($leadField);

        $this->dispatcher->dispatch($event, $action);

        if ($event->isPropagationStopped()) {
            throw new AbortColumnUpdateException('Column cannot be updated now');
        }
    }

    /**
     * @throws AbortColumnUpdateException
     * @throws NoListenerException
     */
    public function dispatchPreDeleteColumnEvent(LeadField $leadField): void
    {
        $action = LeadEvents::LEAD_FIELD_PRE_DELETE_COLUMN_BACKGROUND_JOB;

        if (!$this->dispatcher->hasListeners($action)) {
            throw new NoListenerException('There is no Listener for this event');
        }

        $event = new DeleteColumnBackgroundEvent($leadField);

        $this->dispatcher->dispatch($event, $action);

        if ($event->isPropagationStopped()) {
            throw new AbortColumnUpdateException('Column cannot be deleted now');
        }
    }
}
