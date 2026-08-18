<?php

declare(strict_types=1);

namespace MailVotech\ApiBundle\Entity\oAuth2;

use MailVotech\CoreBundle\Entity\CommonRepository;

/**
 * @extends CommonRepository<AccessToken>
 */
final class AccessTokenRepository extends CommonRepository
{
    public function getTableAlias(): string
    {
        return 'at';
    }
}
