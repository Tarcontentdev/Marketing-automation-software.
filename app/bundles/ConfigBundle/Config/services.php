<?php

declare(strict_types=1);

use MailVotech\CoreBundle\DependencyInjection\MailVotechCoreExtension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $configurator): void {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->public();

    $excludes = [
        'Form/DataTransformer/DsnTransformer.php',
    ];

    $services->load('MailVotech\\ConfigBundle\\', '../')
        ->exclude('../{'.implode(',', array_merge(MailVotechCoreExtension::DEFAULT_EXCLUDES, $excludes)).'}');

    $services->get(MailVotech\ConfigBundle\Form\Type\EscapeTransformer::class)->arg('$allowedParameters', '%mailvotech.config_allowed_parameters%');
    $services->get(MailVotech\ConfigBundle\Form\Helper\RestrictionHelper::class)->arg('$restrictedFields', '%mailvotech.security.restrictedConfigFields%');
    $services->get(MailVotech\ConfigBundle\Form\Helper\RestrictionHelper::class)->arg('$displayMode', '%mailvotech.security.restrictedConfigFields.displayMode%');

    // @deprecated Remove all aliases in MailVotech 6. Use FQCN instead.
    $services->alias('mailvotech.config.model.sysinfo', MailVotech\ConfigBundle\Model\SysinfoModel::class);
    $services->alias('mailvotech.config.mapper', MailVotech\ConfigBundle\Mapper\ConfigMapper::class);
    $services->alias('mailvotech.config.config_change_logger', MailVotech\ConfigBundle\Service\ConfigChangeLogger::class);
    $services->alias('mailvotech.config.form.escape_transformer', MailVotech\ConfigBundle\Form\Type\EscapeTransformer::class);
    $services->alias('mailvotech.config.form.restriction_helper', MailVotech\ConfigBundle\Form\Helper\RestrictionHelper::class);
};
