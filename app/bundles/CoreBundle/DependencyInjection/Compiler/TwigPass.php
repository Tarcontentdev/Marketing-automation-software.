<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\DependencyInjection\Compiler;

use MailVotech\CoreBundle\Twig\Helper\AssetsHelper;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class TwigPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->hasDefinition(AssetsHelper::class)) {
            $container->getDefinition(AssetsHelper::class)
                ->addMethodCall('setPathsHelper', [new Reference('mailvotech.helper.paths')])
                ->addMethodCall('setAssetHelper', [new Reference('mailvotech.helper.assetgeneration')])
                ->addMethodCall('setBuilderIntegrationsHelper', [new Reference('mailvotech.integrations.helper.builder_integrations')])
                ->addMethodCall('setInstallService', [new Reference('mailvotech.install.service')])
                ->addMethodCall('setSiteUrl', ['%mailvotech.site_url%'])
                ->addMethodCall('setVersion', ['%mailvotech.secret_key%', MAILVOTECH_VERSION]);
        }
    }
}
