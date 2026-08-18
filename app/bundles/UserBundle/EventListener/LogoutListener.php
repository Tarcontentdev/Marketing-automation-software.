<?php

namespace MailVotech\UserBundle\EventListener;

use MailVotech\CoreBundle\Helper\UserHelper;
use MailVotech\UserBundle\Event\LogoutEvent;
use MailVotech\UserBundle\UserEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final readonly class LogoutListener implements \Symfony\Component\EventDispatcher\EventSubscriberInterface
{
    private ?\MailVotech\UserBundle\Entity\User $user;

    public function __construct(
        private EventDispatcherInterface $dispatcher,
        UserHelper $userHelper,
    ) {
        $this->user       = $userHelper->getUser();
    }

    public function onLogout(\Symfony\Component\Security\Http\Event\LogoutEvent $logoutEvent): void
    {
        $request = $logoutEvent->getRequest();
        $session = $request->getSession();
        if ($this->dispatcher->hasListeners(UserEvents::USER_LOGOUT)) {
            $mailvotechEvent = new LogoutEvent($this->user, $request);
            $this->dispatcher->dispatch($mailvotechEvent, UserEvents::USER_LOGOUT);
            $sessionItems = $mailvotechEvent->getPostSessionItems();
            foreach ($sessionItems as $key => $value) {
                $session->set($key, $value);
            }
        }
        // Clear session
        $session->clear();

        // Note that a logout occurred
        $session->set('post_logout', true);
    }

    /**
     * @return array<string, mixed>
     */
    public static function getSubscribedEvents(): array
    {
        return [\Symfony\Component\Security\Http\Event\LogoutEvent::class => 'onLogout'];
    }

    public function onSymfonyComponentSecurityHttpEventLogoutEvent(\Symfony\Component\Security\Http\Event\LogoutEvent $logoutEvent): void
    {
        $this->onLogout($logoutEvent);
    }
}
