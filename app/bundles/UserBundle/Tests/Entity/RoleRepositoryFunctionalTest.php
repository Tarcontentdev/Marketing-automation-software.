<?php

declare(strict_types=1);

namespace MailVotech\UserBundle\Tests\Entity;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\UserBundle\Entity\Role;
use MailVotech\UserBundle\Entity\RoleRepository;
use MailVotech\UserBundle\Tests\Traits\CreateEntityTrait;

final class RoleRepositoryFunctionalTest extends MailVotechMysqlTestCase
{
    use CreateEntityTrait;

    public function testGetUserCount(): void
    {
        $roleOne   = $this->createRole();
        $roleTwo   = $this->createRole();
        $this->createUser($roleOne, 'one@example.com');
        $this->createUser($roleOne, 'two@example.com');
        $this->createUser($roleTwo, 'three@example.com');

        $this->em->flush();
        $this->em->clear();

        /** @var RoleRepository $repo */
        $repo = $this->em->getRepository(Role::class);

        $expected = [
            $roleOne->getId() => '2',
            $roleTwo->getId() => '1',
        ];

        $this->assertSame($expected, $repo->getUserCount([$roleOne->getId(), $roleTwo->getId()]));
    }
}
