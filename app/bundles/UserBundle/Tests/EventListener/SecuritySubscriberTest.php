<?php

declare(strict_types=1);

namespace MailVotech\UserBundle\Tests\EventListener;

use MailVotech\CoreBundle\Helper\IpLookupHelper;
use MailVotech\CoreBundle\Model\AuditLogModel;
use MailVotech\UserBundle\Entity\User;
use MailVotech\UserBundle\Event\LoginEvent;
use MailVotech\UserBundle\EventListener\SecuritySubscriber;
use MailVotech\UserBundle\UserEvents;

final class SecuritySubscriberTest extends \PHPUnit\Framework\TestCase
{
    public function testGetSubscribedEvents(): void
    {
        $ipLookupHelper = $this->createStub(IpLookupHelper::class);
        $auditLogModel  = $this->createStub(AuditLogModel::class);
        $subscriber     = new SecuritySubscriber($ipLookupHelper, $auditLogModel);

        $this->assertSame(
            [
                UserEvents::USER_LOGIN => ['onSecurityInteractiveLogin', 0],
            ],
            $subscriber->getSubscribedEvents()
        );
    }

    public function testOnSecurityInteractiveLogin(): void
    {
        $userId   = 132564;
        $userName = 'John Doe';
        $ip       = '125.55.45.21';
        $log      = [
            'bundle'    => 'user',
            'object'    => 'security',
            'objectId'  => $userId,
            'action'    => 'login',
            'details'   => ['username' => $userName],
            'ipAddress' => $ip,
        ];

        $ipLookupHelper = $this->createMock(IpLookupHelper::class);
        $ipLookupHelper->expects($this->once())
            ->method('getIpAddressFromRequest')
            ->willReturn($ip);
        $auditLogModel = $this->createMock(AuditLogModel::class);
        $auditLogModel->expects($this->once())
            ->method('writeToLog')
            ->with($log);
        $user = $this->createMock(User::class);
        $user->expects($this->once())
            ->method('getId')
            ->willReturn($userId);
        $user->expects($this->once())
            ->method('getUserIdentifier')
            ->willReturn($userName);
        $event = $this->createMock(LoginEvent::class);
        $event->expects($this->exactly(2))
            ->method('getUser')
            ->willReturn($user);
        $subscriber = new SecuritySubscriber($ipLookupHelper, $auditLogModel);

        $subscriber->onSecurityInteractiveLogin($event);
    }
}
