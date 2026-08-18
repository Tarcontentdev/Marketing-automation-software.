<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle;

use MailVotech\IntegrationsBundle\Bundle\AbstractPluginBundle;
use MailVotech\IntegrationsBundle\DependencyInjection\Compiler\TestPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class IntegrationsBundle extends AbstractPluginBundle
{
    public function build(ContainerBuilder $container): void
    {
        if ('test' === $container->getParameter('kernel.environment')) {
            $container->addCompilerPass(new TestPass());
        }
    }
}
