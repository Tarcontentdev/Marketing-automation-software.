<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechSocialBundle;

use MailVotech\PluginBundle\Bundle\PluginBundleBase;

final class MailVotechSocialBundle extends PluginBundleBase
{

    public function getContainerExtension(): \Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        if (!$this->extension instanceof \Symfony\Component\DependencyInjection\Extension\ExtensionInterface) {
            $this->extension = new \MailVotechPlugin\MailVotechSocialBundle\DependencyInjection\MailVotechSocialExtension();
        }

        return $this->extension;
    }

}
