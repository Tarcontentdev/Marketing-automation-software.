<?php

declare(strict_types=1);

namespace MailVotech\DynamicContentBundle\DynamicContent;

final class TypeList
{
    public const HTML = 'html';

    public const TEXT = 'text';

    /**
     * @return string[]
     */
    public function getChoices(): array
    {
        return [
            'mailvotech.dynamic.content.type.html' => self::HTML,
            'mailvotech.dynamic.content.type.text' => self::TEXT,
        ];
    }
}
