<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Integration\Interfaces;

use MailVotech\IntegrationsBundle\DTO\Note;

interface ConfigFormNotesInterface
{
    public function getAuthorizationNote(): ?Note;

    public function getFeaturesNote(): ?Note;

    public function getFieldMappingNote(): ?Note;
}
