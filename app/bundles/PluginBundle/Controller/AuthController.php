<?php

namespace MailVotech\PluginBundle\Controller;

use MailVotech\CoreBundle\Controller\FormController;
use MailVotech\PluginBundle\Event\PluginIntegrationAuthRedirectEvent;
use MailVotech\PluginBundle\Helper\IntegrationHelper;
use MailVotech\PluginBundle\PluginEvents;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class AuthController extends FormController
{
    /**
     * @param string $integration
     */
    public function authCallbackAction(Request $request, IntegrationHelper $integrationHelper, $integration): JsonResponse|RedirectResponse
    {
        $isAjax  = $request->isXmlHttpRequest();
        $session = $request->getSession();

        $integrationObject = $integrationHelper->getIntegrationObject($integration);

        // check to see if the service exists
        if (!$integrationObject) {
            $session->set('mailvotech.integration.postauth.message', ['mailvotech.integration.notfound', ['%name%' => $integration], 'error']);
            if ($isAjax) {
                return new JsonResponse(['url' => $this->generateUrl('mailvotech_integration_auth_postauth', ['integration' => $integration])]);
            }

            return new RedirectResponse($this->generateUrl('mailvotech_integration_auth_postauth', ['integration' => $integration]));
        }

        try {
            $error = $integrationObject->authCallback();
        } catch (\InvalidArgumentException $e) {
            $session->set('mailvotech.integration.postauth.message', [$e->getMessage(), [], 'error']);
            $redirectUrl = $this->generateUrl('mailvotech_integration_auth_postauth', ['integration' => $integration]);
            if ($isAjax) {
                return new JsonResponse(['url' => $redirectUrl]);
            }

            return new RedirectResponse($redirectUrl);
        }

        // check for error
        if ($error) {
            $type    = 'error';
            $message = 'mailvotech.integration.error.oauthfail';
            $params  = ['%error%' => $error];
        } else {
            $type    = 'notice';
            $message = 'mailvotech.integration.notice.oauthsuccess';
            $params  = [];
        }

        $session->set('mailvotech.integration.postauth.message', [$message, $params, $type]);

        $identifier[$integration] = null;
        $socialCache              = [];
        $userData                 = $integrationObject->getUserData($identifier, $socialCache);

        $session->set('mailvotech.integration.'.$integration.'.userdata', $userData);

        return new RedirectResponse($this->generateUrl('mailvotech_integration_auth_postauth', ['integration' => $integration]));
    }

    public function authStatusAction(Request $request, $integration): Response
    {
        $postAuthTemplate = '@MailVotechPlugin/Auth/postauth.html.twig';

        $session     = $request->getSession();
        $postMessage = $session->get('mailvotech.integration.postauth.message');
        $userData    = [];

        if (isset($integration)) {
            $userData = $session->get('mailvotech.integration.'.$integration.'.userdata');
        }

        $message = $type = '';
        $alert   = 'success';
        if (!empty($postMessage)) {
            $message = $this->translator->trans($postMessage[0], $postMessage[1], 'flashes');
            $session->remove('mailvotech.integration.postauth.message');
            $type = $postMessage[2];
            if ('error' == $type) {
                $alert = 'danger';
            }
        }

        return $this->render($postAuthTemplate, ['message' => $message, 'alert' => $alert, 'data' => $userData]);
    }

    public function authUserAction(IntegrationHelper $integrationHelper, $integration): RedirectResponse
    {
        $integrationObject = $integrationHelper->getIntegrationObject($integration);

        $settings['method']      = 'GET';
        $settings['integration'] = $integrationObject->getName();

        /** @var \MailVotech\PluginBundle\Integration\AbstractIntegration $integrationObject */
        $event = $this->dispatcher->dispatch(
            new PluginIntegrationAuthRedirectEvent(
                $integrationObject,
                $integrationObject->getAuthLoginUrl()
            ),
            PluginEvents::PLUGIN_ON_INTEGRATION_AUTH_REDIRECT
        );
        $oauthUrl = $event->getAuthUrl();

        return new RedirectResponse($oauthUrl);
    }
}
