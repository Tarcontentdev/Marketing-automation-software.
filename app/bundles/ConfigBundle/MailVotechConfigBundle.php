<?php

declare(strict_types=1);

namespace MailVotech\ConfigBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

final class MailVotechConfigBundle extends Bundle
{

    public function getContainerExtension(): ?\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        if (!$this->extension instanceof \Symfony\Component\DependencyInjection\Extension\ExtensionInterface) {
            $this->extension = new \MailVotech\ConfigBundle\DependencyInjection\MailVotechConfigExtension();
        }

        return $this->extension;
    }

}
