<?php

declare(strict_types=1);

namespace MailVotech\UserBundle\Tests\Security;

use MailVotech\CoreBundle\Helper\UserHelper;
use MailVotech\CoreBundle\Test\AbstractMailVotechTestCase;
use MailVotech\UserBundle\Entity\User;
use MailVotech\UserBundle\Model\UserModel;
use MailVotech\UserBundle\Security\UserTokenSetter;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class UserTokenSetterTest extends AbstractMailVotechTestCase
{
    public function testSetUserMakesTheUserAvailableToUserHelper(): void
    {
        /** @var MockObject&UserModel $userModel */
        $userModel = $this->createMock(UserModel::class);
        $user      = new User();

        $userModel->method('getEntity')
            ->with(1)
            ->willReturn($user);

        $userTokenSetter = new UserTokenSetter($userModel, $this->getContainer()->get(TokenStorageInterface::class));

        $userTokenSetter->setUser(1);

        /** @var UserHelper $userHelper */
        $userHelper = $this->getContainer()->get(UserHelper::class);

        $this->assertSame($user, $userHelper->getUser());
    }
}
