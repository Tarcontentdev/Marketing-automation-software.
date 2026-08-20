<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechGmailBundle;

use MailVotech\PluginBundle\Bundle\PluginBundleBase;

final class MailVotechGmailBundle extends PluginBundleBase
{

    public function getContainerExtension(): \Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        if (!$this->extension instanceof \Symfony\Component\DependencyInjection\Extension\ExtensionInterface) {
            $this->extension = new \MailVotechPlugin\MailVotechGmailBundle\DependencyInjection\MailVotechGmailExtension();
        }

        return $this->extension;
    }

}
