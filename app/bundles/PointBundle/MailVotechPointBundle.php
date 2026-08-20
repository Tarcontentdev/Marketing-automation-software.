<?php

declare(strict_types=1);

namespace MailVotech\PointBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

final class MailVotechPointBundle extends Bundle
{

    public function getContainerExtension(): \Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        if (!$this->extension instanceof \Symfony\Component\DependencyInjection\Extension\ExtensionInterface) {
            $this->extension = new \MailVotech\PointBundle\DependencyInjection\MailVotechPointExtension();
        }

        return $this->extension;
    }

}
