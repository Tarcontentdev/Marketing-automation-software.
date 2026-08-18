<?php

declare(strict_types=1);

namespace MailVotech\NotificationBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

final class MailVotechNotificationBundle extends Bundle
{

    public function getContainerExtension(): ?\Symfony\Component\DependencyInjection\Extension\ExtensionInterface
    {
        if (!$this->extension instanceof \Symfony\Component\DependencyInjection\Extension\ExtensionInterface) {
            $this->extension = new \MailVotech\NotificationBundle\DependencyInjection\MailVotechNotificationExtension();
        }

        return $this->extension;
    }

}
