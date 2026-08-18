<?php

declare(strict_types=1);

namespace MailVotech\FormBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

final class MailVotechFormBundle extends Bundle
{

    public function getContainerExtension(): ?\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        if (!$this->extension instanceof \Symfony\Component\DependencyInjection\Extension\ExtensionInterface) {
            $this->extension = new \MailVotech\FormBundle\DependencyInjection\MailVotechFormExtension();
        }

        return $this->extension;
    }

}
