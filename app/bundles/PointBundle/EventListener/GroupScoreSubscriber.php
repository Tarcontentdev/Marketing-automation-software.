<?php

declare(strict_types=1);

namespace MailVotech\PointBundle\EventListener;

use MailVotech\PointBundle\Event\GroupScoreChangeEvent;
use MailVotech\PointBundle\Model\TriggerModel;
use MailVotech\PointBundle\PointGroupEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class GroupScoreSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private TriggerModel $triggerModel,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PointGroupEvents::SCORE_CHANGE     => ['onGroupScoreChange', 0],
        ];
    }

    public function onGroupScoreChange(GroupScoreChangeEvent $event): void
    {
        $this->triggerModel->triggerEvents($event->getContact());
    }
}
