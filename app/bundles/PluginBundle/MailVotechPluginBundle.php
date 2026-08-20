<?php

declare(strict_types=1);

namespace MailVotech\PluginBundle;

use MailVotech\PluginBundle\Tests\DependencyInjection\Compiler\TestPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class MailVotechPluginBundle extends Bundle
{

    public function getContainerExtension(): \Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        if (!$this->extension instanceof \Symfony\Component\DependencyInjection\Extension\ExtensionInterface) {
            $this->extension = new \MailVotech\PluginBundle\DependencyInjection\MailVotechPluginExtension();
        }

        return $this->extension;
    }

    public function build(ContainerBuilder $container): void
    {
        if ('test' === $container->getParameter('kernel.environment')) {
            $container->addCompilerPass(new TestPass());
        }
    }
}
