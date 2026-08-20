<?php

declare(strict_types=1);

namespace MailVotech\ProjectBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

final class MailVotechProjectBundle extends Bundle
{

    public function getContainerExtension(): \Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        if (!$this->extension instanceof \Symfony\Component\DependencyInjection\Extension\ExtensionInterface) {
            $this->extension = new \MailVotech\ProjectBundle\DependencyInjection\MailVotechProjectExtension();
        }

        return $this->extension;
    }

}
