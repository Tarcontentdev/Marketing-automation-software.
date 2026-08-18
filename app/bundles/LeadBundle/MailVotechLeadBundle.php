<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

final class MailVotechLeadBundle extends Bundle
{

    public function getContainerExtension(): ?\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        if (!$this->extension instanceof \Symfony\Component\DependencyInjection\Extension\ExtensionInterface) {
            $this->extension = new \MailVotech\LeadBundle\DependencyInjection\MailVotechLeadExtension();
        }

        return $this->extension;
    }

}
