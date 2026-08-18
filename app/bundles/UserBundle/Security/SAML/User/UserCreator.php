<?php

namespace MailVotech\UserBundle\Security\SAML\User;

use Doctrine\ORM\EntityManagerInterface;
use LightSaml\Model\Protocol\Response;
use LightSaml\SpBundle\Security\User\UserCreatorInterface;
use MailVotech\CoreBundle\Helper\EncryptionHelper;
use MailVotech\UserBundle\Entity\Role;
use MailVotech\UserBundle\Entity\User;
use MailVotech\UserBundle\Model\UserModel;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

final class UserCreator implements UserCreatorInterface
{
    private readonly int $defaultRole;

    private array $requiredFields = [
        'username',
        'firstname',
        'lastname',
        'email',
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserMapper $userMapper,
        private readonly UserModel $userModel,
        $defaultRole,
    ) {
        $this->defaultRole   = (int) $defaultRole;
    }

    public function createUser(Response $response): User
    {
        if (empty($this->defaultRole)) {
            throw new BadCredentialsException('User does not exist.');
        }

        /** @var Role $defaultRole */
        $defaultRole = $this->entityManager->getReference(Role::class, $this->defaultRole);

        $user = $this->userMapper->getUser($response);
        $user->setPassword($this->userModel->checkNewPassword($user, EncryptionHelper::generateKey()));
        $user->setRole($defaultRole);

        $this->validateUser($user);

        $this->userModel->saveEntity($user);

        return $user;
    }

    /**
     * @throws BadCredentialsException
     */
    private function validateUser(User $user): void
    {
        // Validate that the user has all that's required
        foreach ($this->requiredFields as $field) {
            $getter = 'get'.ucfirst($field);

            if (!$user->{$getter}()) {
                throw new BadCredentialsException('User does not include required fields.');
            }
        }
    }
}
