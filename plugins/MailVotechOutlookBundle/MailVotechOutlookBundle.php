<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechOutlookBundle;

use MailVotech\PluginBundle\Bundle\PluginBundleBase;

final class MailVotechOutlookBundle extends PluginBundleBase
{

    public function getContainerExtension(): \Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        if (!$this->extension instanceof \Symfony\Component\DependencyInjection\Extension\ExtensionInterface) {
            $this->extension = new \MailVotechPlugin\MailVotechOutlookBundle\DependencyInjection\MailVotechOutlookExtension();
        }

        return $this->extension;
    }

}
