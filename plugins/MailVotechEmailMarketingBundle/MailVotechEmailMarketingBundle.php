<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechEmailMarketingBundle;

use MailVotech\PluginBundle\Bundle\PluginBundleBase;

final class MailVotechEmailMarketingBundle extends PluginBundleBase
{

    public function getContainerExtension(): \Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        if (!$this->extension instanceof \Symfony\Component\DependencyInjection\Extension\ExtensionInterface) {
            $this->extension = new \MailVotechPlugin\MailVotechEmailMarketingBundle\DependencyInjection\MailVotechEmailMarketingExtension();
        }

        return $this->extension;
    }

}
