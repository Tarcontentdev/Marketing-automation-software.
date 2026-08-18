<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\Functional\ApiPlatform;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\UserBundle\Entity\Role;
use MailVotech\UserBundle\Entity\User;
use MailVotech\UserBundle\Model\RoleModel;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

abstract class OwnershipScopedApiAuthorizationTestBase extends MailVotechMysqlTestCase
{
    /**
     * @param array<string, array<string>> $permissions
     */
    protected function createUserWithPermissions(string $username, string $email, string $password, array $permissions): User
    {
        $role = new Role();
        $role->setName('role_'.$username);
        $role->setIsAdmin(false);

        /** @var RoleModel $roleModel */
        $roleModel = static::getContainer()->get(RoleModel::class);
        $roleModel->setRolePermissions($role, $permissions);

        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setFirstName('Test');
        $user->setLastName('User');
        $user->setRole($role);

        $hasher = static::getContainer()->get(PasswordHasherFactoryInterface::class)->getPasswordHasher($user);
        $this->assertInstanceOf(PasswordHasherInterface::class, $hasher);
        $user->setPassword($hasher->hash($password));

        $this->em->persist($role);
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
