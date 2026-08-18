<?php

declare(strict_types=1);

namespace MailVotech\MarketplaceBundle\Service;

use Symfony\Component\Routing\RouterInterface;

final readonly class RouteProvider
{
    public const ROUTE_LIST = 'mailvotech_marketplace_list';

    public const ROUTE_DETAIL = 'mailvotech_marketplace_detail';

    public const ROUTE_INSTALL = 'mailvotech_marketplace_install';

    public const ROUTE_REMOVE = 'mailvotech_marketplace_remove';

    public const ROUTE_CLEAR_CACHE = 'mailvotech_marketplace_clear_cache';

    public function __construct(
        private RouterInterface $router,
    ) {
    }

    public function buildListRoute(int $page = 1): string
    {
        return $this->router->generate(self::ROUTE_LIST, ['page' => $page]);
    }

    public function buildDetailRoute(string $vendor, string $package): string
    {
        return $this->router->generate(
            self::ROUTE_DETAIL,
            ['vendor' => $vendor, 'package' => $package]
        );
    }

    public function buildInstallRoute(string $vendor, string $package): string
    {
        return $this->router->generate(
            self::ROUTE_DETAIL,
            ['vendor' => $vendor, 'package' => $package]
        );
    }

    public function buildRemoveRoute(string $vendor, string $package): string
    {
        return $this->router->generate(
            self::ROUTE_REMOVE,
            ['vendor' => $vendor, 'package' => $package]
        );
    }

    public function buildClearCacheRoute(): string
    {
        return $this->router->generate(
            self::ROUTE_CLEAR_CACHE
        );
    }
}
