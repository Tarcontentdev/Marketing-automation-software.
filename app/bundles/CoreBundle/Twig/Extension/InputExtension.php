<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Twig\Extension;

use MailVotech\CoreBundle\Helper\InputHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class InputExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('inputUrl', InputHelper::url(...)),
            new TwigFunction('inputAlphanum', InputHelper::alphanum(...)),
            new TwigFunction('inputTransliterate', InputHelper::transliterate(...)),
            new TwigFunction('inputClean', InputHelper::clean(...)),
        ];
    }
}
