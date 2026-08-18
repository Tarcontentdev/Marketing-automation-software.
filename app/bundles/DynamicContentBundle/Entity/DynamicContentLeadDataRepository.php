<?php

declare(strict_types=1);

namespace MailVotech\DynamicContentBundle\Entity;

use MailVotech\CoreBundle\Entity\CommonRepository;

/**
 * @extends CommonRepository<DynamicContentLeadData>
 */
final class DynamicContentLeadDataRepository extends CommonRepository
{
    public function getTableAlias(): string
    {
        return 'dcld';
    }
}
