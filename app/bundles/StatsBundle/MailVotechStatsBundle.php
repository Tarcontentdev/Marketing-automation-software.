<?php

declare(strict_types=1);

namespace MailVotech\StatsBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

final class MailVotechStatsBundle extends Bundle
{

    public function getContainerExtension(): \Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        if (!$this->extension instanceof \Symfony\Component\DependencyInjection\Extension\ExtensionInterface) {
            $this->extension = new \MailVotech\StatsBundle\DependencyInjection\MailVotechStatsExtension();
        }

        return $this->extension;
    }

}
