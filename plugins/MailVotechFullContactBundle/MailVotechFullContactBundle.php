<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechFullContactBundle;

use MailVotech\PluginBundle\Bundle\PluginBundleBase;

final class MailVotechFullContactBundle extends PluginBundleBase
{

    public function getContainerExtension(): ?\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        if (!$this->extension instanceof \Symfony\Component\DependencyInjection\Extension\ExtensionInterface) {
            $this->extension = new \MailVotechPlugin\MailVotechFullContactBundle\DependencyInjection\MailVotechFullContactExtension();
        }

        return $this->extension;
    }

}
