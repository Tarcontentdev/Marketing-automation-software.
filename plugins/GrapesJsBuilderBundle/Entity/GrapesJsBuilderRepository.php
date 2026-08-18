<?php

declare(strict_types=1);

namespace MailVotechPlugin\GrapesJsBuilderBundle\Entity;

use MailVotech\CoreBundle\Entity\CommonRepository;

/**
 * @extends CommonRepository<GrapesJsBuilder>
 */
class GrapesJsBuilderRepository extends CommonRepository
{
    public function getTableAlias(): string
    {
        return 'gjb';
    }
}
