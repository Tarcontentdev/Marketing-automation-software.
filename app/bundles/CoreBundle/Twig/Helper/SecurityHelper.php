<?php

namespace MailVotech\CoreBundle\Twig\Helper;

use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\UserBundle\Entity\User;
use MailVotech\UserBundle\Event\AuthenticationContentEvent;
use MailVotech\UserBundle\UserEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * final class SecurityHelper.
 */
final readonly class SecurityHelper
{
    public function __construct(
        private CorePermissions $security,
        private RequestStack $requestStack,
        private EventDispatcherInterface $dispatcher,
        private CsrfTokenManagerInterface $tokenManager,
    ) {
    }

    public function getName(): string
    {
        return 'security';
    }

    /**
     * Helper function to check if user is an Admin.
     */
    public function isAdmin(): bool
    {
        return $this->security->isAdmin();
    }

    /**
     * Helper function to check if the logged in user has access to an entity.
     *
     * @param string|bool $ownPermission
     * @param string|bool $otherPermission
     * @param User|int    $ownerId
     */
    public function hasEntityAccess($ownPermission, $otherPermission, $ownerId): bool
    {
        return $this->security->hasEntityAccess($ownPermission, $otherPermission, $ownerId);
    }

    public function isGranted(string $permission): bool
    {
        return $this->security->isGranted($permission);
    }

    /**
     * Get content from listeners.
     */
    public function getAuthenticationContent(): string
    {
        $request = $this->requestStack->getCurrentRequest();
        $content = '';
        if ($this->dispatcher->hasListeners(UserEvents::USER_AUTHENTICATION_CONTENT)) {
            $event = new AuthenticationContentEvent($request);
            $this->dispatcher->dispatch($event, UserEvents::USER_AUTHENTICATION_CONTENT);
            $content = $event->getContent();

            // Remove post_logout session after content has been generated
            $request->getSession()->remove('post_logout');
        }

        return $content;
    }

    /**
     * Returns CSRF token string for an intention.
     */
    public function getCsrfToken(string $intention): string
    {
        return $this->tokenManager->getToken($intention)->getValue();
    }
}
