<?php

declare(strict_types=1);

namespace MailVotech\CampaignBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class MailVotechCampaignBundle extends Bundle
{

    public function getContainerExtension(): \Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        if (!$this->extension instanceof \Symfony\Component\DependencyInjection\Extension\ExtensionInterface) {
            $this->extension = new \MailVotech\CampaignBundle\DependencyInjection\MailVotechCampaignExtension();
        }

        return $this->extension;
    }

    public function build(ContainerBuilder $container): void
    {
    }
}
