<?php

declare(strict_types=1);

namespace MailVotech\UserBundle\Entity;

interface UserInviteRepositoryInterface
{
    public function findOneByTokenSelector(string $selector): ?UserInvite;

    public function revokeOutstandingInvites(string $email): int;
}
