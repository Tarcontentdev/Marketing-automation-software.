<?php

declare(strict_types=1);

use FOS\OAuthServerBundle\Form\Handler\AuthorizeFormHandler;
use MailVotech\CoreBundle\DependencyInjection\MailVotechCoreExtension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return function (ContainerConfigurator $configurator): void {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->public();

    $excludes = [
        'Serializer/Exclusion',
        'Helper/BatchIdToEntityHelper.php',
    ];

    $services->load('MailVotech\\ApiBundle\\', '../')
        ->exclude('../{'.implode(',', array_merge(MailVotechCoreExtension::DEFAULT_EXCLUDES, $excludes)).'}');

    $services->load('MailVotech\\ApiBundle\\Entity\\oAuth2\\', '../Entity/oAuth2/*Repository.php');
    $services->set('mailvotech.api.helper.entity_result', MailVotech\ApiBundle\Helper\EntityResultHelper::class);

    $services->set(MailVotech\ApiBundle\EventListener\PreAuthorizationEventListener::class);

    $services->set('mailvotech.validator.oauthcallback', MailVotech\ApiBundle\Form\Validator\Constraints\OAuthCallbackValidator::class)->tag('validator.constraint_validator');
    $services->set('mailvotech.api.security.voter.permission', MailVotech\ApiBundle\Security\Voter\ApiPermissionVoter::class)->tag('security.voter');

    $services->alias(AuthorizeFormHandler::class, 'fos_oauth_server.authorize.form.handler.default');

    $services->get(MailVotech\ApiBundle\Controller\oAuth2\AuthorizeController::class)
        ->arg('$authorizeForm', service('fos_oauth_server.authorize.form'))
        ->arg('$oAuth2Server', service('fos_oauth_server.server'))
        ->arg('$clientManager', service('fos_oauth_server.client_manager.default'))
        ->tag('controller.service_arguments');

    $services->alias('mailvotech.api.model.client', MailVotech\ApiBundle\Model\ClientModel::class);

    // Register custom PUT processor to fix PUT operations globally
    // This ensures PUT requests update existing entities instead of creating new ones
    // This decorates the default persist processor so it applies to all entities automatically
    $services->set(MailVotech\ApiBundle\State\PutProcessor::class)
        ->decorate('api_platform.doctrine.orm.state.persist_processor')
        ->args([
            service('.inner'),
            service('doctrine.orm.entity_manager'),
        ]);
};
