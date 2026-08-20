<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechClearbitBundle;

use MailVotech\PluginBundle\Bundle\PluginBundleBase;

final class MailVotechClearbitBundle extends PluginBundleBase
{

    public function getContainerExtension(): \Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        if (!$this->extension instanceof \Symfony\Component\DependencyInjection\Extension\ExtensionInterface) {
            $this->extension = new \MailVotechPlugin\MailVotechClearbitBundle\DependencyInjection\MailVotechClearbitExtension();
        }

        return $this->extension;
    }

}
