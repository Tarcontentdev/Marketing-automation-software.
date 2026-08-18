<?php

declare(strict_types=1);

namespace MailVotech\UserBundle\Security;

interface UserTokenSetterInterface
{
    public function setUser(int $userId): void;
}
