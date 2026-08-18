<?php

declare(strict_types=1);

namespace MailVotech\UserBundle\Tests\Event;

use MailVotech\UserBundle\Entity\User;
use MailVotech\UserBundle\Event\LoginEvent;

final class LoginEventTest extends \PHPUnit\Framework\TestCase
{
    public function testGetUser(): void
    {
        $user  = $this->createStub(User::class);
        $event = new LoginEvent($user);

        $this->assertEquals($user, $event->getUser());
    }
}
