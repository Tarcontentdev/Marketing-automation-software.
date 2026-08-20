<?php

namespace MailVotech\UserBundle;

use MailVotech\UserBundle\DependencyInjection\Compiler\FormLoginAuthenticatorOptionsPass;
use MailVotech\UserBundle\DependencyInjection\Compiler\OAuthReplacePass;
use MailVotech\UserBundle\DependencyInjection\Compiler\SsoAuthenticatorPass;
use MailVotech\UserBundle\DependencyInjection\Firewall\Factory\MailVotechSsoFactory;
use MailVotech\UserBundle\DependencyInjection\Firewall\Factory\PluginFactory;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class MailVotechUserBundle extends Bundle
{

    public function getContainerExtension(): \Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        if (!$this->extension instanceof \Symfony\Component\DependencyInjection\Extension\ExtensionInterface) {
            $this->extension = new \MailVotech\UserBundle\DependencyInjection\MailVotechUserExtension();
        }

        return $this->extension;
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        \assert($extension instanceof SecurityExtension);
        $extension->addAuthenticatorFactory(new PluginFactory());
        $extension->addAuthenticatorFactory(new MailVotechSsoFactory());

        $container->addCompilerPass(new OAuthReplacePass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 0);
        $container->addCompilerPass(new SsoAuthenticatorPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 0);
        $container->addCompilerPass(new FormLoginAuthenticatorOptionsPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 0);
    }
}
