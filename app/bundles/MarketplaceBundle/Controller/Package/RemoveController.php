<?php

declare(strict_types=1);

namespace MailVotech\MarketplaceBundle\Controller\Package;

use MailVotech\CoreBundle\Controller\CommonController;
use MailVotech\MarketplaceBundle\Model\PackageModel;
use MailVotech\MarketplaceBundle\Security\Permissions\MarketplacePermissions;
use MailVotech\MarketplaceBundle\Service\Config;
use MailVotech\MarketplaceBundle\Service\RouteProvider;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\Required;

final class RemoveController extends CommonController
{
    private PackageModel $packageModel;

    private RouteProvider $routeProvider;

    private Config $config;

    #[Required]
    public function autowireRemoveController(
        PackageModel $packageModel,
        RouteProvider $routeProvider,
        Config $config,
    ): void {
        $this->packageModel  = $packageModel;
        $this->routeProvider = $routeProvider;
        $this->config        = $config;
    }

    public function viewAction(string $vendor, string $package): Response
    {
        if (!$this->config->marketplaceIsEnabled()) {
            return $this->notFound();
        }

        if (!$this->security->isGranted(MarketplacePermissions::CAN_REMOVE_PACKAGES)) {
            $this->throwAccessDenied();
        }

        return $this->delegateView(
            [
                'returnUrl'      => $this->routeProvider->buildListRoute(),
                'viewParameters' => [
                    'packageDetail'  => $this->packageModel->getPackageDetail("{$vendor}/{$package}"),
                ],
                'contentTemplate' => '@Marketplace/Package/remove.html.twig',
                'passthroughVars' => [
                    'mailvotechContent' => 'package',
                    'activeLink'    => '#mailvotech_marketplace',
                    'route'         => $this->routeProvider->buildRemoveRoute($vendor, $package),
                ],
            ]
        );
    }
}
