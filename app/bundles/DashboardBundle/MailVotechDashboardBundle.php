<?php

declare(strict_types=1);

namespace MailVotech\DashboardBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

final class MailVotechDashboardBundle extends Bundle
{

    public function getContainerExtension(): ?\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        if (!$this->extension instanceof \Symfony\Component\DependencyInjection\Extension\ExtensionInterface) {
            $this->extension = new \MailVotech\DashboardBundle\DependencyInjection\MailVotechDashboardExtension();
        }

        return $this->extension;
    }

}
