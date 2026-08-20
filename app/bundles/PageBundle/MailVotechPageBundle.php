<?php

declare(strict_types=1);

namespace MailVotech\PageBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

final class MailVotechPageBundle extends Bundle
{

    public function getContainerExtension(): \Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        if (!$this->extension instanceof \Symfony\Component\DependencyInjection\Extension\ExtensionInterface) {
            $this->extension = new \MailVotech\PageBundle\DependencyInjection\MailVotechPageExtension();
        }

        return $this->extension;
    }

}
