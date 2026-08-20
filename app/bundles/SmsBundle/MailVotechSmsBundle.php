<?php

namespace MailVotech\SmsBundle;

use MailVotech\PluginBundle\Bundle\PluginBundleBase;
use MailVotech\SmsBundle\DependencyInjection\Compiler\SmsTransportPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class MailVotechSmsBundle extends PluginBundleBase
{

    public function getContainerExtension(): \Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        if (!$this->extension instanceof \Symfony\Component\DependencyInjection\Extension\ExtensionInterface) {
            $this->extension = new \MailVotech\SmsBundle\DependencyInjection\MailVotechSmsExtension();
        }

        return $this->extension;
    }

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new SmsTransportPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 0);
    }
}
