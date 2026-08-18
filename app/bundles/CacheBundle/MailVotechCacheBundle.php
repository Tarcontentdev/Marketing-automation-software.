<?php

declare(strict_types=1);

namespace MailVotech\CacheBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

final class MailVotechCacheBundle extends Bundle
{

    public function getContainerExtension(): ?\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        if (!$this->extension instanceof \Symfony\Component\DependencyInjection\Extension\ExtensionInterface) {
            $this->extension = new \MailVotech\CacheBundle\DependencyInjection\MailVotechCacheExtension();
        }

        return $this->extension;
    }

}
