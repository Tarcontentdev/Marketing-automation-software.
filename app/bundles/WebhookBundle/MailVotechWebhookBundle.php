<?php

declare(strict_types=1);

namespace MailVotech\WebhookBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

final class MailVotechWebhookBundle extends Bundle
{

    public function getContainerExtension(): ?\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        if (!$this->extension instanceof \Symfony\Component\DependencyInjection\Extension\ExtensionInterface) {
            $this->extension = new \MailVotech\WebhookBundle\DependencyInjection\MailVotechWebhookExtension();
        }

        return $this->extension;
    }

}
