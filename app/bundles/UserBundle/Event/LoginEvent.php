<?php

declare(strict_types=1);

namespace MailVotech\UserBundle\Event;

use MailVotech\UserBundle\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class LoginEvent extends Event
{
    public function __construct(
        private readonly User $user,
    ) {
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
