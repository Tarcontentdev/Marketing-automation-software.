<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\DependencyInjection;

use MailVotech\IntegrationsBundle\Integration\Interfaces\BasicInterface;
use MailVotech\IntegrationsBundle\Integration\Interfaces\BuilderInterface;
use MailVotech\IntegrationsBundle\Integration\Interfaces\ConfigFormInterface;
use MailVotech\IntegrationsBundle\Integration\Interfaces\IntegrationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class IntegrationsExtension extends Extension
{
    /**
     * @param mixed[] $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Config'));
        $loader->load('services.php');

        $container->registerForAutoconfiguration(IntegrationInterface::class)
            ->addTag('mailvotech.integration');
        $container->registerForAutoconfiguration(BasicInterface::class)
            ->addTag('mailvotech.basic_integration');
        $container->registerForAutoconfiguration(ConfigFormInterface::class)
            ->addTag('mailvotech.config_integration');
        $container->registerForAutoconfiguration(BuilderInterface::class)
            ->addTag('mailvotech.builder_integration');
    }
}
