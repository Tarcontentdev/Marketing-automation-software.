<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\EventListener;

use MailVotech\CoreBundle\CoreEvents;
use MailVotech\CoreBundle\Event\CustomAssetsEvent;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class EditorFontsSubscriber implements EventSubscriberInterface
{
    public const PARAMETER_EDITOR_FONTS = 'editor_fonts';

    public function __construct(
        private CoreParametersHelper $coreParametersHelper,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CoreEvents::VIEW_INJECT_CUSTOM_ASSETS => ['addGlobalAssets', 0],
        ];
    }

    public function addGlobalAssets(CustomAssetsEvent $customAssetsEvent): void
    {
        $this->addEditorFonts($customAssetsEvent);
    }

    private function addEditorFonts(CustomAssetsEvent $customAssetsEvent): void
    {
        $fonts = (array) $this->coreParametersHelper->get(self::PARAMETER_EDITOR_FONTS, []);
        foreach ($fonts as $font) {
            if (empty($font['url'])) {
                continue;
            }

            $customAssetsEvent->addStylesheet($font['url']);
        }
    }
}
