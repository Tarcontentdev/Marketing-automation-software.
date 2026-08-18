<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Twig\Extension;

use MailVotech\CoreBundle\Helper\Serializer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class SerializerExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('serializerDecode', Serializer::decode(...)),
        ];
    }
}
