<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Doctrine\Provider;

interface VersionProviderInterface
{
    public function getVersion(): string;

    public function isMariaDb(): bool;

    public function isMySql(): bool;
}
