<?php

declare(strict_types=1);

namespace MailVotech\UserBundle\Tests\Traits;

use MailVotech\UserBundle\Entity\Role;
use MailVotech\UserBundle\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

trait CreateEntityTrait
{
    public function createRole(bool $isAdmin = false): Role
    {
        $role = new Role();
        $role->setName('Role');
        $role->setIsAdmin($isAdmin);
        $this->em->persist($role);

        return $role;
    }

    public function createUser(Role $role, string $email = 'test@acquia.com', string $password = 'mailvotech'): User
    {
        $userName = explode('@', $email)[0].random_int(1000, 9999);
        $user     = new User();
        $user->setFirstName('John');
        $user->setLastName('Doe');
        $user->setUsername($userName);
        $user->setEmail($email);
        $encoder = $this->getContainer()->get(UserPasswordHasherInterface::class);
        $user->setPassword($encoder->hashPassword($user, $password));
        $user->setRole($role);
        $this->em->persist($user);

        return $user;
    }
}
