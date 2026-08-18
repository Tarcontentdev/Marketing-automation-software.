<?php

namespace MailVotechPlugin\MailVotechFocusBundle\EventListener;

use MailVotech\FormBundle\Event\SubmissionEvent;
use MailVotech\FormBundle\FormEvents;
use MailVotech\PageBundle\Event\PageHitEvent;
use MailVotech\PageBundle\PageEvents;
use MailVotechPlugin\MailVotechFocusBundle\Entity\Stat;
use MailVotechPlugin\MailVotechFocusBundle\Model\FocusModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final readonly class StatSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private FocusModel $model,
        private RequestStack $requestStack,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PageEvents::PAGE_ON_HIT    => ['onPageHit', 0],
            FormEvents::FORM_ON_SUBMIT => ['onFormSubmit', 0],
        ];
    }

    public function onPageHit(PageHitEvent $event): void
    {
        $hit    = $event->getHit();
        $source = $hit->getSource();

        if ('focus' == $source || 'focus.focus' == $source) {
            $sourceId = $hit->getSourceId();
            $focus    = $this->model->getEntity($sourceId);

            if ($focus && $focus->isPublished()) {
                $this->model->addStat($focus, Stat::TYPE_CLICK, $hit, $hit->getLead());
            }
        }
    }

    /**
     * Note if this submission is from a focus submit.
     */
    public function onFormSubmit(SubmissionEvent $event): void
    {
        // Check the request for a focus field
        $mailvotechform = $this->requestStack->getCurrentRequest()->request->all()['mailvotechform'] ?? [];
        $id         = $mailvotechform['focusId'] ?? false;

        if (!empty($id)) {
            $focus = $this->model->getEntity($id);

            if ($focus && $focus->isPublished()) {
                // Make sure the form is still applicable
                $form = $event->getSubmission()->getForm();
                if ((int) $form->getId() === (int) $focus->getForm()) {
                    $this->model->addStat($focus, Stat::TYPE_FORM, $event->getSubmission(), $event->getLead());
                }
            }
        }
    }
}
