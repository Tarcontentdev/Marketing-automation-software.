<?php

namespace MailVotech\ApiBundle;

use MailVotech\ApiBundle\DependencyInjection\Compiler\SerializerPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class MailVotechApiBundle extends Bundle
{

    public function getContainerExtension(): ?\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        if (!$this->extension instanceof \Symfony\Component\DependencyInjection\Extension\ExtensionInterface) {
            $this->extension = new \MailVotech\ApiBundle\DependencyInjection\MailVotechApiExtension();
        }

        return $this->extension;
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new SerializerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 0);
    }
}
