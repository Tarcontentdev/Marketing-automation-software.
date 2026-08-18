<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Helper\ListParser;

use MailVotech\CoreBundle\Helper\ListParser\Exception\FormatNotSupportedException;

final class ValueListParser implements ListParserInterface
{
    public function parse($list): array
    {
        if (is_array($list)) {
            throw new FormatNotSupportedException();
        }

        return [$list => $list];
    }
}
