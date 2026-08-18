<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Entity;

use MailVotech\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;

interface UuidInterface
{
    public function getUuid(): ?string;

    public function setUuid(string $uuid): void;

    public static function addUuidField(ClassMetadataBuilder $builder): void;
}
