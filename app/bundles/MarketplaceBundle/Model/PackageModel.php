<?php

declare(strict_types=1);

namespace MailVotech\MarketplaceBundle\Model;

use MailVotech\MarketplaceBundle\Api\Connection;
use MailVotech\MarketplaceBundle\DTO\PackageDetail;
use MailVotech\MarketplaceBundle\Service\Allowlist;

class PackageModel
{
    public function __construct(
        private readonly Connection $connection,
        private readonly Allowlist $allowlist,
    ) {
    }

    public function getPackageDetail(string $name): PackageDetail
    {
        $allowlist      = $this->allowlist->getAllowList();
        $allowedPackage = $allowlist->findPackageByName($name);
        $payload        = $this->connection->getPackage($name);

        return PackageDetail::fromArray($payload['package'] + $allowedPackage->toArray());
    }
}
