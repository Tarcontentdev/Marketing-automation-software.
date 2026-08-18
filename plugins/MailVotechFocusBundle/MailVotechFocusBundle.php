<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechFocusBundle;

use MailVotech\PluginBundle\Bundle\PluginBundleBase;

final class MailVotechFocusBundle extends PluginBundleBase
{

    public function getContainerExtension(): ?\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        if (!$this->extension instanceof \Symfony\Component\DependencyInjection\Extension\ExtensionInterface) {
            $this->extension = new \MailVotechPlugin\MailVotechFocusBundle\DependencyInjection\MailVotechFocusExtension();
        }

        return $this->extension;
    }

}
