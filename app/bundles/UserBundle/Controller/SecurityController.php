<?php

declare(strict_types=1);

namespace MailVotech\UserBundle\Controller;

use MailVotech\CoreBundle\Controller\CommonController;
use MailVotech\CoreBundle\Service\FlashBag;
use MailVotech\PluginBundle\Helper\IntegrationHelper;
use MailVotech\UserBundle\Exception\WeakPasswordException;
use MailVotech\UserBundle\Security\SAML\Helper as SAMLHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Translation\TranslatorInterface;

final class SecurityController extends CommonController implements EventSubscriberInterface
{
    private AuthorizationCheckerInterface $authorizationChecker;

    #[Required]
    public function autowireSecurityController(
        AuthorizationCheckerInterface $authorizationChecker,
    ): void {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function onRequest(RequestEvent $event): void
    {
        $controller = $event->getRequest()->attributes->get('_controller');
        \assert(is_string($controller));

        if (!str_contains($controller, self::class)) {
            return;
        }

        // redirect user if they are already authenticated
        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')
            || $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')
        ) {
            $redirectUrl = $this->generateUrl('mailvotech_dashboard_index');
            $event->setResponse(new RedirectResponse($redirectUrl));
        }
    }

    /**
     * Generates login form and processes login.
     */
    public function loginAction(Request $request, AuthenticationUtils $authenticationUtils, IntegrationHelper $integrationHelper, TranslatorInterface $translator): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();

        if (null !== $error) {
            if ($error instanceof WeakPasswordException) {
                $this->addFlash(FlashBag::LEVEL_ERROR, $translator->trans('mailvotech.user.auth.error.weakpassword', [], 'flashes'));

                return $this->forward('MailVotech\UserBundle\Controller\PublicController::passwordResetAction');
            }
            if ($error instanceof Exception\BadCredentialsException) {
                $msg = 'mailvotech.user.auth.error.invalidlogin';
            } elseif ($error instanceof Exception\DisabledException) {
                $msg = 'mailvotech.user.auth.error.disabledaccount';
            } elseif ($error instanceof Exception\AuthenticationException) {
                $msg = $error->getMessageKey();
            } else {
                $msg = $error->getMessage();
            }

            $messageVars = $error instanceof Exception\AuthenticationException ? $error->getMessageData() : [];
            $this->addFlashMessage($msg, $messageVars, FlashBag::LEVEL_ERROR, null);
        }
        $request->query->set('tmpl', 'login');

        // Get a list of SSO integrations
        $integrations = $integrationHelper->getIntegrationObjects(null, ['sso_service'], true, null, true);

        return $this->delegateView([
            'viewParameters' => [
                'last_username' => $authenticationUtils->getLastUsername(),
                'integrations'  => $integrations,
            ],
            'contentTemplate' => '@MailVotechUser/Security/login.html.twig',
            'passthroughVars' => [
                'route'          => $this->generateUrl('login'),
                'mailvotechContent'  => 'user',
                'sessionExpired' => true,
            ],
        ]);
    }

    /**
     * The plugin should be handling this in it's listener.
     */
    public function ssoLoginAction($integration): RedirectResponse
    {
        return new RedirectResponse($this->generateUrl('login'));
    }

    /**
     * The plugin should be handling this in it's listener.
     */
    public function ssoLoginCheckAction($integration): RedirectResponse
    {
        // The plugin should be handling this in it's listener

        return new RedirectResponse($this->generateUrl('login'));
    }

    public function samlLoginRetryAction(Request $request, SAMLHelper $samlHelper, SessionInterface $session): Response
    {
        if (!$samlHelper->isSamlEnabled()) {
            return new RedirectResponse($this->generateUrl('login'));
        }

        $session->invalidate();

        $this->addFlashMessage('mailvotech.user.security.saml.clearsession', [], FlashBag::LEVEL_ERROR);

        return $this->delegateView([
            'viewParameters' => [
                'loginRoute' => $this->generateUrl('lightsaml_sp.discovery'),
            ],
            'contentTemplate' => '@MailVotechUser/Security/saml_login_retry.html.twig',
            'passthroughVars' => [
                'route'          => $this->generateUrl('mailvotech_base_index'),
                'mailvotechContent'  => 'user',
                'sessionExpired' => true,
            ],
        ]);
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onRequest',
        ];
    }
}
