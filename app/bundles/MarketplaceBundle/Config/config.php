<?php

declare(strict_types=1);

use MailVotech\MarketplaceBundle\Service\Config;
use MailVotech\MarketplaceBundle\Service\RouteProvider;

return [
    'routes' => [
        'main' => [
            RouteProvider::ROUTE_LIST => [
                'path'       => '/marketplace/{page}',
                'controller' => 'MailVotech\MarketplaceBundle\Controller\Package\ListController::listAction',
                'method'     => 'GET|POST',
                'defaults'   => ['page' => 1],
            ],
            RouteProvider::ROUTE_DETAIL => [
                'path'       => '/marketplace/detail/{vendor}/{package}',
                'controller' => 'MailVotech\MarketplaceBundle\Controller\Package\DetailController::viewAction',
                'method'     => 'GET',
            ],
            RouteProvider::ROUTE_INSTALL => [
                'path'       => '/marketplace/install/{vendor}/{package}',
                'controller' => 'MailVotech\MarketplaceBundle\Controller\Package\InstallController::viewAction',
                'method'     => 'GET|POST',
            ],
            RouteProvider::ROUTE_REMOVE => [
                'path'       => '/marketplace/remove/{vendor}/{package}',
                'controller' => 'MailVotech\MarketplaceBundle\Controller\Package\RemoveController::viewAction',
                'method'     => 'GET|POST',
            ],
            RouteProvider::ROUTE_CLEAR_CACHE => [
                'path'       => '/marketplace/clear/cache',
                'controller' => 'MailVotech\MarketplaceBundle\Controller\CacheController::clearAction',
                'method'     => 'GET',
            ],
        ],
    ],
    // NOTE: when adding new parameters here, please add them to the developer documentation as well:
    'parameters' => [
        Config::MARKETPLACE_ENABLED                     => true,
        Config::MARKETPLACE_ALLOWLIST_URL               => 'https://raw.githubusercontent.com/mailvotech/marketplace-allowlist/main/allowlist.json',
        Config::MARKETPLACE_ALLOWLIST_CACHE_TTL_SECONDS => 3600,
    ],
];
