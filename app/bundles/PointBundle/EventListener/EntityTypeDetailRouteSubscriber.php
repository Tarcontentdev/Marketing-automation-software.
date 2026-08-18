<?php

declare(strict_types=1);

namespace MailVotech\PointBundle\EventListener;

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
        // Point entity uses edit
        $event->addRoute('point', new DetailRoute(
            'mailvotech_point_action',
            'objectId',
            ['objectAction' => 'edit']
        ));
    }
}
