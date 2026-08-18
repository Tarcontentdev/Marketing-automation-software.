<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechTagManagerBundle;

use MailVotech\PluginBundle\Bundle\PluginBundleBase;

final class MailVotechTagManagerBundle extends PluginBundleBase
{

    public function getContainerExtension(): ?\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        if (!$this->extension instanceof \Symfony\Component\DependencyInjection\Extension\ExtensionInterface) {
            $this->extension = new \MailVotechPlugin\MailVotechTagManagerBundle\DependencyInjection\MailVotechTagManagerExtension();
        }

        return $this->extension;
    }

}
