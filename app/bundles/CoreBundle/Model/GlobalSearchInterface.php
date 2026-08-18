<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Model;

use Doctrine\ORM\Tools\Pagination\Paginator;
use MailVotech\CoreBundle\DTO\GlobalSearchFilterDTO;

interface GlobalSearchInterface
{
    public function getEntitiesForGlobalSearch(GlobalSearchFilterDTO $searchFilter): ?Paginator;

    public function canViewOwnEntity(): bool;

    public function canViewOthersEntity(): bool;
}
