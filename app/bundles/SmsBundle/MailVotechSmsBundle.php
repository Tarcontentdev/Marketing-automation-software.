<?php

namespace MailVotech\SmsBundle;

use MailVotech\PluginBundle\Bundle\PluginBundleBase;
use MailVotech\SmsBundle\DependencyInjection\Compiler\SmsTransportPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class MailVotechSmsBundle extends PluginBundleBase
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new SmsTransportPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 0);
    }
}
