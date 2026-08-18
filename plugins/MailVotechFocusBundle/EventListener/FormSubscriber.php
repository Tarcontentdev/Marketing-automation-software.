<?php

namespace MailVotechPlugin\MailVotechFocusBundle\EventListener;

use MailVotech\FormBundle\Event as Events;
use MailVotech\FormBundle\FormEvents;
use MailVotechPlugin\MailVotechFocusBundle\Entity\FocusRepository;
use MailVotechPlugin\MailVotechFocusBundle\Model\FocusModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class FormSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private FocusModel $model,
        private FocusRepository $focusRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::FORM_POST_DELETE => ['onFormDelete', 0],
        ];
    }

    /**
     * Add a delete entry to the audit log.
     */
    public function onFormDelete(Events\FormEvent $event): void
    {
        $form   = $event->getForm();
        $formId = $form->deletedId;
        $foci   = $this->focusRepository->findByForm($formId);

        if (empty($foci)) {
            return;
        }

        // Rebuild each focus
        /** @var \MailVotechPlugin\MailVotechFocusBundle\Entity\Focus $focus */
        foreach ($foci as $focus) {
            $focus->setForm(null);
        }

        $this->model->saveEntities($foci);
    }
}
