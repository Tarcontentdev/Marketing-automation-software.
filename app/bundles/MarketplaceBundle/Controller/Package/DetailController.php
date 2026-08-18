<?php

declare(strict_types=1);

namespace MailVotech\MarketplaceBundle\Controller\Package;

use MailVotech\CoreBundle\Controller\CommonController;
use MailVotech\CoreBundle\Helper\ComposerHelper;
use MailVotech\MarketplaceBundle\Exception\RecordNotFoundException;
use MailVotech\MarketplaceBundle\Model\PackageModel;
use MailVotech\MarketplaceBundle\Security\Permissions\MarketplacePermissions;
use MailVotech\MarketplaceBundle\Service\Config;
use MailVotech\MarketplaceBundle\Service\RouteProvider;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\Required;

final class DetailController extends CommonController
{
    private PackageModel $packageModel;

    private RouteProvider $routeProvider;

    private Config $config;

    private ComposerHelper $composer;

    #[Required]
    public function autowireDetailController(
        PackageModel $packageModel,
        RouteProvider $routeProvider,
        Config $config,
        ComposerHelper $composer,
    ): void {
        $this->packageModel  = $packageModel;
        $this->routeProvider = $routeProvider;
        $this->config        = $config;
        $this->composer      = $composer;
    }

    public function viewAction(string $vendor, string $package): Response
    {
        if (!$this->config->marketplaceIsEnabled()) {
            return $this->notFound();
        }

        if (!$this->security->isGranted(MarketplacePermissions::CAN_VIEW_PACKAGES)) {
            $this->throwAccessDenied();
        }

        $isInstalled = $this->composer->isInstalled("{$vendor}/{$package}");

        try {
            $packageDetail = $this->packageModel->getPackageDetail("{$vendor}/{$package}");
        } catch (RecordNotFoundException $e) {
            return $this->notFound($e->getMessage());
        }

        $security = $this->security;

        return $this->delegateView(
            [
                'returnUrl'      => $this->routeProvider->buildListRoute(),
                'viewParameters' => [
                    'packageDetail'     => $packageDetail,
                    'isInstalled'       => $isInstalled,
                    'isComposerEnabled' => $this->config->isComposerEnabled(),
                    'security'          => $security,
                ],
                'contentTemplate' => '@Marketplace/Package/detail.html.twig',
                'passthroughVars' => [
                    'mailvotechContent' => 'package',
                    'activeLink'    => '#mailvotech_marketplace',
                    'route'         => $this->routeProvider->buildDetailRoute($vendor, $package),
                ],
            ]
        );
    }
}
