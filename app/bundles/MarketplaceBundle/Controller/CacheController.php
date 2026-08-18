<?php

declare(strict_types=1);

namespace MailVotech\MarketplaceBundle\Controller;

use MailVotech\CoreBundle\Controller\CommonController;
use MailVotech\MarketplaceBundle\Security\Permissions\MarketplacePermissions;
use MailVotech\MarketplaceBundle\Service\Allowlist;
use MailVotech\MarketplaceBundle\Service\Config;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\Required;

final class CacheController extends CommonController
{
    private Config $config;

    private Allowlist $allowlist;

    #[Required]
    public function autowireCacheController(
        Config $config,
        Allowlist $allowlist,
    ): void {
        $this->config    = $config;
        $this->allowlist = $allowlist;
    }

    public function clearAction(): Response
    {
        if (!$this->config->marketplaceIsEnabled()) {
            return $this->notFound();
        }

        if (!$this->security->isGranted(MarketplacePermissions::CAN_VIEW_PACKAGES)) {
            $this->throwAccessDenied();
        }

        $this->allowlist->clearCache();

        return $this->forward(
            'MailVotech\MarketplaceBundle\Controller\Package\ListController::listAction'
        );
    }
}
