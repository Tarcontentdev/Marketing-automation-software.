<?php

declare(strict_types=1);

namespace MailVotech\MessengerBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

final class MailVotechMessengerBundle extends Bundle
{

    public function getContainerExtension(): ?\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        if (!$this->extension instanceof \Symfony\Component\DependencyInjection\Extension\ExtensionInterface) {
            $this->extension = new \MailVotech\MessengerBundle\DependencyInjection\MailVotechMessengerExtension();
        }

        return $this->extension;
    }

}
