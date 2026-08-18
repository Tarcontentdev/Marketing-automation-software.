<?php

namespace MailVotech\ApiBundle;

use MailVotech\ApiBundle\DependencyInjection\Compiler\SerializerPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class MailVotechApiBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new SerializerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 0);
    }
}
