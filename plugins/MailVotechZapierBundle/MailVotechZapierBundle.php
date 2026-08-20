<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechZapierBundle;

use MailVotech\PluginBundle\Bundle\PluginBundleBase;

final class MailVotechZapierBundle extends PluginBundleBase
{

    public function getContainerExtension(): \Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        if (!$this->extension instanceof \Symfony\Component\DependencyInjection\Extension\ExtensionInterface) {
            $this->extension = new \MailVotechPlugin\MailVotechZapierBundle\DependencyInjection\MailVotechZapierExtension();
        }

        return $this->extension;
    }

}
