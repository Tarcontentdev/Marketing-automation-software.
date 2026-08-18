<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Sync\Notification\Helper;

use MailVotech\IntegrationsBundle\Event\InternalObjectRouteEvent;
use MailVotech\IntegrationsBundle\IntegrationEvents;
use MailVotech\IntegrationsBundle\Sync\Exception\ObjectNotFoundException;
use MailVotech\IntegrationsBundle\Sync\Exception\ObjectNotSupportedException;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Internal\ObjectProvider;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\MailVotechSyncDataExchange;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RouteHelper
{
    public function __construct(
        private readonly ObjectProvider $objectProvider,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    /**
     * @throws ObjectNotSupportedException
     */
    public function getRoute(string $object, int $id): string
    {
        try {
            $event = new InternalObjectRouteEvent($this->objectProvider->getObjectByName($object), $id);
        } catch (ObjectNotFoundException) {
            // Throw this exception instead to keep BC.
            throw new ObjectNotSupportedException(MailVotechSyncDataExchange::NAME, $object);
        }

        $this->dispatcher->dispatch($event, IntegrationEvents::INTEGRATION_BUILD_INTERNAL_OBJECT_ROUTE);

        return $event->getRoute();
    }

    /**
     * @throws ObjectNotSupportedException
     */
    public function getLink(string $object, int $id, string $linkText): string
    {
        $route = $this->getRoute($object, $id);

        return sprintf('<a href="%s">%s</a>', $route, $linkText);
    }

    /**
     * @throws ObjectNotSupportedException
     */
    public function getRoutes(string $object, array $ids): array
    {
        $routes = [];
        foreach ($ids as $id) {
            $routes[$id] = $this->getRoute($object, $id);
        }

        return $routes;
    }

    /**
     * @throws ObjectNotSupportedException
     */
    public function getLinkCsv(string $object, array $ids): string
    {
        $links  = [];
        $routes = $this->getRoutes($object, $ids);
        foreach ($routes as $id => $route) {
            $links[] = sprintf('[<a href="%s">%s</a>]', $route, $id);
        }

        return implode(', ', $links);
    }
}
