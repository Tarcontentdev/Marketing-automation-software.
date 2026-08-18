<?php

declare(strict_types=1);

namespace MailVotech\UserBundle\EventListener;

use MailVotech\CoreBundle\Helper\IpLookupHelper;
use MailVotech\CoreBundle\Model\AuditLogModel;
use MailVotech\UserBundle\Event\LoginEvent;
use MailVotech\UserBundle\UserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class SecuritySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private IpLookupHelper $ipLookupHelper,
        private AuditLogModel $auditLogModel,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserEvents::USER_LOGIN => ['onSecurityInteractiveLogin', 0],
        ];
    }

    public function onSecurityInteractiveLogin(LoginEvent $event): void
    {
        $userId   = (int) $event->getUser()->getId();
        $useName  = $event->getUser()->getUserIdentifier();

        $log     = [
            'bundle'    => 'user',
            'object'    => 'security',
            'objectId'  => $userId,
            'action'    => 'login',
            'details'   => ['username' => $useName],
            'ipAddress' => $this->ipLookupHelper->getIpAddressFromRequest(),
        ];

        $this->auditLogModel->writeToLog($log);
    }
}
