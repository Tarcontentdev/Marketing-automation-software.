<?php

declare(strict_types=1);

namespace MailVotech\UserBundle\Tests\Model;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\UserBundle\Entity\RoleRepository;
use MailVotech\UserBundle\Entity\User;
use MailVotech\UserBundle\Form\Validator\Constraints\NotWeak;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class PasswordStrengthEstimatorModelTest extends MailVotechMysqlTestCase
{
    private PasswordHasherFactoryInterface $passwordHasher;

    private RoleRepository $roleRepository;

    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->passwordHasher = self::getContainer()->get(PasswordHasherFactoryInterface::class);
        $this->roleRepository = self::getContainer()->get(RoleRepository::class);
        $this->validator      = self::getContainer()->get(ValidatorInterface::class);
    }

    public function testThatItIsNotPossibleToCreateAnUserWithAWeakPassword(): void
    {
        $simplePassword = '11111111';

        $user = new User();
        $user->setFirstName('First Name');
        $user->setLastName('LastName');
        $user->setUsername('username');
        $user->setEmail('some@email.domain');
        $user->setPlainPassword($simplePassword);
        $user->setPassword($this->passwordHasher->getPasswordHasher($user)->hash($simplePassword));
        $user->setRole($this->roleRepository->findAll()[0]);
        $violations                    = $this->validator->validate($user);
        $hasNotWeakConstraintViolation = false;

        /** @var ConstraintViolation $violation */
        foreach ($violations as $violation) {
            $hasNotWeakConstraintViolation |= $violation->getConstraint() instanceof NotWeak;
        }

        $this->assertGreaterThanOrEqual(1, count($violations));
        $this->assertTrue((bool) $hasNotWeakConstraintViolation);
    }
}
