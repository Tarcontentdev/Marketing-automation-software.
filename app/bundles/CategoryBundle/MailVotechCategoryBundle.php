<?php

declare(strict_types=1);

namespace MailVotech\CategoryBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

final class MailVotechCategoryBundle extends Bundle
{

    public function getContainerExtension(): ?\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        if (!$this->extension instanceof \Symfony\Component\DependencyInjection\Extension\ExtensionInterface) {
            $this->extension = new \MailVotech\CategoryBundle\DependencyInjection\MailVotechCategoryExtension();
        }

        return $this->extension;
    }

}
