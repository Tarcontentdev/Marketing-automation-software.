<?php

declare(strict_types=1);

namespace MailVotech\MarketplaceBundle\Controller\Package;

use MailVotech\CoreBundle\Controller\CommonController;
use MailVotech\CoreBundle\Helper\InputHelper;
use MailVotech\MarketplaceBundle\Security\Permissions\MarketplacePermissions;
use MailVotech\MarketplaceBundle\Service\Config;
use MailVotech\MarketplaceBundle\Service\PluginCollector;
use MailVotech\MarketplaceBundle\Service\RouteProvider;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\Required;

final class ListController extends CommonController
{
    private PluginCollector $pluginCollector;

    private RouteProvider $routeProvider;

    private Config $config;

    #[Required]
    public function autowireListController(
        PluginCollector $pluginCollector,
        RouteProvider $routeProvider,
        Config $config,
    ): void {
        $this->pluginCollector = $pluginCollector;
        $this->routeProvider   = $routeProvider;
        $this->config          = $config;
    }

    public function listAction(int $page = 1): Response
    {
        if (!$this->config->marketplaceIsEnabled()) {
            return $this->notFound();
        }

        if (!$this->security->isGranted(MarketplacePermissions::CAN_VIEW_PACKAGES)) {
            $this->throwAccessDenied();
        }

        $this->setListFilters();

        $request = $this->getCurrentRequest();
        $search  = InputHelper::clean($request->get('search', ''));

        $session = $request->getSession();
        if (empty($page)) {
            $page = $session->get('mailvotech.marketplace.package.page', 1);
        }

        // set limits
        $limit   = $session->get('mailvotech.marketplace.package.limit', $this->coreParametersHelper->get('default_pagelimit'));
        $route   = $this->routeProvider->buildListRoute($page);

        return $this->delegateView(
            [
                'returnUrl'      => $route,
                'viewParameters' => [
                    'searchValue'       => $search,
                    'items'             => $this->pluginCollector->collectPackages($page, $limit, $search),
                    'count'             => $this->pluginCollector->getTotal(),
                    'page'              => $page,
                    'limit'             => $limit,
                    'tmpl'              => $request->isXmlHttpRequest() ? $request->get('tmpl', 'index') : 'index',
                    'isComposerEnabled' => $this->config->isComposerEnabled(),
                ],
                'contentTemplate' => '@Marketplace/Package/list.html.twig',
                'passthroughVars' => [
                    'mailvotechContent' => 'package',
                    'route'         => $route,
                ],
            ]
        );
    }
}
