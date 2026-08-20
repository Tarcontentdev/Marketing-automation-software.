<?php

declare(strict_types=1);

namespace MailVotech\AssetBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

final class MailVotechAssetBundle extends Bundle
{

    public function getContainerExtension(): \Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        if (!$this->extension instanceof \Symfony\Component\DependencyInjection\Extension\ExtensionInterface) {
            $this->extension = new \MailVotech\AssetBundle\DependencyInjection\MailVotechAssetExtension();
        }

        return $this->extension;
    }

}
