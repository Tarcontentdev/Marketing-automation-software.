<?php

namespace MailVotech\UserBundle\Model\UserToken;

use MailVotech\CoreBundle\Helper\RandomHelper\RandomHelperInterface;
use MailVotech\UserBundle\Entity\UserToken;
use MailVotech\UserBundle\Entity\UserTokenRepositoryInterface;

final readonly class UserTokenService implements UserTokenServiceInterface
{
    public function __construct(
        private RandomHelperInterface $randomHelper,
        private UserTokenRepositoryInterface $userTokenRepository,
    ) {
    }

    /**
     * @param int $secretLength
     */
    public function generateSecret(UserToken $token, $secretLength = 32): UserToken
    {
        do {
            $randomSecret   = $this->randomHelper->generate($secretLength);
            $isSecretUnique = $this->userTokenRepository->isSecretUnique($randomSecret);
        } while (false === $isSecretUnique);

        return $token->setSecret($randomSecret);
    }

    /**
     * @return bool
     */
    public function verify(UserToken $token)
    {
        return $this->userTokenRepository->verify($token);
    }
}
