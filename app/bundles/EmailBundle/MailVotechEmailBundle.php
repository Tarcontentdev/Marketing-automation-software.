<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

final class MailVotechEmailBundle extends Bundle
{

    public function getContainerExtension(): ?\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        if (!$this->extension instanceof \Symfony\Component\DependencyInjection\Extension\ExtensionInterface) {
            $this->extension = new \MailVotech\EmailBundle\DependencyInjection\MailVotechEmailExtension();
        }

        return $this->extension;
    }

}
