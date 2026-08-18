<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Twig\Extension;

use MailVotech\CoreBundle\Helper\ThemeHelper;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ThemeExtension extends AbstractExtension
{
    public function __construct(
        private readonly ThemeHelper $themeHelper,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('getThemeName', $this->getThemeName(...)),
        ];
    }

    /**
     * Get the theme display name for the specified theme.
     */
    public function getThemeName(string $theme = 'current'): string
    {
        // Special case for Code Mode
        if ('mailvotech_code_mode' === $theme) {
            return $this->translator->trans('mailvotech.core.code.mode');
        }

        $themeConfig = $this->themeHelper->getTheme($theme)->getConfig();

        return $themeConfig['name'] ?? $theme;
    }
}
