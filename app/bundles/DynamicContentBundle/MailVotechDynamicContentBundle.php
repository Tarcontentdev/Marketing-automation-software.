<?php

declare(strict_types=1);

namespace MailVotech\DynamicContentBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

final class MailVotechDynamicContentBundle extends Bundle
{

    public function getContainerExtension(): ?\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        if (!$this->extension instanceof \Symfony\Component\DependencyInjection\Extension\ExtensionInterface) {
            $this->extension = new \MailVotech\DynamicContentBundle\DependencyInjection\MailVotechDynamicContentExtension();
        }

        return $this->extension;
    }

}
