<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\EventListener;

use MailVotech\EmailBundle\Model\EmailModel;
use MailVotech\LeadBundle\Event\ListChangeEvent;
use MailVotech\LeadBundle\LeadEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class SegmentSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EmailModel $emailModel,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LeadEvents::LEAD_LIST_CHANGE       => ['onListChange', 0],
            LeadEvents::LEAD_LIST_BATCH_CHANGE => ['onListChange', 0],
        ];
    }

    public function onListChange(ListChangeEvent $event): void
    {
        $this->emailModel->invalidatePendingCountCacheForList($event->getList()->getId());
    }
}
