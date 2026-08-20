<?php

declare(strict_types=1);

namespace MailVotech\ChannelBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

final class MailVotechChannelBundle extends Bundle
{

    public function getContainerExtension(): \Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        if (!$this->extension instanceof \Symfony\Component\DependencyInjection\Extension\ExtensionInterface) {
            $this->extension = new \MailVotech\ChannelBundle\DependencyInjection\MailVotechChannelExtension();
        }

        return $this->extension;
    }

}
