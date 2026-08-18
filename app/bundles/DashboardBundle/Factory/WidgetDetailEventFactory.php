<?php

declare(strict_types=1);

namespace MailVotech\DashboardBundle\Factory;

use MailVotech\CacheBundle\Cache\CacheProviderTagAwareInterface;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Helper\PathsHelper;
use MailVotech\CoreBundle\Helper\UserHelper;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\DashboardBundle\Entity\Widget;
use MailVotech\DashboardBundle\Event\WidgetDetailEvent;
use Symfony\Contracts\Translation\TranslatorInterface;

class WidgetDetailEventFactory
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly CacheProviderTagAwareInterface $cacheProvider,
        private readonly CorePermissions $corePermissions,
        private readonly UserHelper $userHelper,
        private readonly CoreParametersHelper $coreParametersHelper,
        private readonly PathsHelper $pathsHelper,
    ) {
    }

    public function create(Widget $widget): WidgetDetailEvent
    {
        $cacheDir = $this->coreParametersHelper->get('cached_data_dir', $this->pathsHelper->getSystemPath('cache', true));
        $event    = new WidgetDetailEvent($this->translator, $this->corePermissions, $widget, $this->cacheProvider);
        $event->setCacheDir($cacheDir, $this->userHelper->getUser()->getId());

        return $event;
    }
}
