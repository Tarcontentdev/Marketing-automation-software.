<?php

declare(strict_types=1);

namespace MailVotech\PointBundle\EventListener;

use MailVotech\PointBundle\Event\GroupScoreChangeEvent;
use MailVotech\PointBundle\Model\InsightModel;
use MailVotech\PointBundle\PointGroupEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class PointInsightSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private InsightModel $insightModel,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PointGroupEvents::SCORE_CHANGE => ['onGroupScoreChange', 0],
        ];
    }

    public function onGroupScoreChange(GroupScoreChangeEvent $event): void
    {
        $this->insightModel->executePointInsights($event->getContact(), $event->getGroup());
    }
}
