<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\EventListener;

use MailVotech\CoreBundle\CoreEvents;
use MailVotech\CoreBundle\Event\CustomAssetsEvent;
use MailVotech\CoreBundle\Twig\Helper\AssetsHelper;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class AssetsSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private AssetsHelper $assetsHelper,
        private EventDispatcherInterface $dispatcher,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['fetchCustomAssets', 0],
        ];
    }

    public function fetchCustomAssets(RequestEvent $event): void
    {
        if ($event->isMainRequest() && $this->dispatcher->hasListeners(CoreEvents::VIEW_INJECT_CUSTOM_ASSETS)) {
            $this->dispatcher->dispatch(
                new CustomAssetsEvent($this->assetsHelper),
                CoreEvents::VIEW_INJECT_CUSTOM_ASSETS
            );
        }
    }
}
