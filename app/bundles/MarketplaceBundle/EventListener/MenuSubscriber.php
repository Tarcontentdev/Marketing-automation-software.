<?php

declare(strict_types=1);

namespace MailVotech\MarketplaceBundle\EventListener;

use MailVotech\CoreBundle\CoreEvents;
use MailVotech\CoreBundle\Event\MenuEvent;
use MailVotech\MarketplaceBundle\Security\Permissions\MarketplacePermissions;
use MailVotech\MarketplaceBundle\Service\Config;
use MailVotech\MarketplaceBundle\Service\RouteProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class MenuSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Config $config,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CoreEvents::BUILD_MENU => ['onBuildMenu', 9999],
        ];
    }

    public function onBuildMenu(MenuEvent $event): void
    {
        if ('admin' !== $event->getType() || !$this->config->marketplaceIsEnabled()) {
            return;
        }

        $event->addMenuItems(
            [
                'priority' => 81,
                'items'    => [
                    'marketplace.title' => [
                        'id'        => 'marketplace',
                        'route'     => RouteProvider::ROUTE_LIST,
                        'access'    => MarketplacePermissions::CAN_VIEW_PACKAGES,
                        'parent'    => 'mailvotech.core.integrations',
                        'iconClass' => 'ri-shopping-bag-2-line',
                        'priority'  => 16,
                    ],
                ],
            ]
        );
    }
}
