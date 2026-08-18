<?php

declare(strict_types=1);

namespace MailVotech\UserBundle\Model\UserToken;

use MailVotech\UserBundle\Entity\UserToken;

/**
 * Interface UserTokenServiceInterface.
 */
interface UserTokenServiceInterface
{
    /**
     * @param int $secretLength
     *
     * @return UserToken
     */
    public function generateSecret(UserToken $token, $secretLength = 32);

    /**
     * @return bool
     */
    public function verify(UserToken $token);
}
