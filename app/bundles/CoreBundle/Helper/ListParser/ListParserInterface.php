<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Helper\ListParser;

use MailVotech\CoreBundle\Helper\ListParser\Exception\FormatNotSupportedException;

interface ListParserInterface
{
    /**
     * @param mixed $list
     *
     * @throws FormatNotSupportedException
     */
    public function parse($list): array;
}
