<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Controller;

use MailVotech\CoreBundle\Controller\CommonController;
use MailVotech\IntegrationsBundle\Exception\IntegrationNotFoundException;
use MailVotech\IntegrationsBundle\Exception\UnauthorizedException;
use MailVotech\IntegrationsBundle\Helper\AuthIntegrationsHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class AuthController extends CommonController
{
    public function callbackAction(AuthIntegrationsHelper $authIntegrationsHelper, string $integration, Request $request): Response
    {
        $authenticationError = false;

        try {
            $authIntegration = $authIntegrationsHelper->getIntegration($integration);
            $message         = $authIntegration->authenticateIntegration($request);
        } catch (UnauthorizedException $exception) {
            $message             = $exception->getMessage();
            $authenticationError = true;
        } catch (IntegrationNotFoundException) {
            return $this->notFound();
        }

        return $this->render(
            '@Integrations/Auth/authenticated.html.twig',
            [
                'message'             => $message,
                'authenticationError' => $authenticationError,
            ]
        );
    }
}
