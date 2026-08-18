<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Doctrine\Provider;

use MailVotech\CoreBundle\Doctrine\GeneratedColumn\GeneratedColumns;

interface GeneratedColumnsProviderInterface
{
    public function getGeneratedColumns(): GeneratedColumns;

    public function generatedColumnsAreSupported(): bool;

    public function getMinimalSupportedVersion(): string;
}
