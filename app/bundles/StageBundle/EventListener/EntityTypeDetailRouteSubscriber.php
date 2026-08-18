<?php

declare(strict_types=1);

namespace MailVotech\StageBundle\EventListener;

use MailVotech\ProjectBundle\DTO\DetailRoute;
use MailVotech\ProjectBundle\Event\EntityTypeDetailRouteEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class EntityTypeDetailRouteSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            EntityTypeDetailRouteEvent::class => 'onEntityTypeDetailRoute',
        ];
    }

    public function onEntityTypeDetailRoute(EntityTypeDetailRouteEvent $event): void
    {
        $event->addRoute('stage', new DetailRoute(
            'mailvotech_stage_action',
            'objectId',
            ['objectAction' => 'edit']
        ));
    }
}
