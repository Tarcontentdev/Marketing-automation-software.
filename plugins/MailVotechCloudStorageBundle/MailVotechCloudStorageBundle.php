<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechCloudStorageBundle;

use MailVotech\PluginBundle\Bundle\PluginBundleBase;

final class MailVotechCloudStorageBundle extends PluginBundleBase
{

    public function getContainerExtension(): \Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        if (!$this->extension instanceof \Symfony\Component\DependencyInjection\Extension\ExtensionInterface) {
            $this->extension = new \MailVotechPlugin\MailVotechCloudStorageBundle\DependencyInjection\MailVotechCloudStorageExtension();
        }

        return $this->extension;
    }

}
