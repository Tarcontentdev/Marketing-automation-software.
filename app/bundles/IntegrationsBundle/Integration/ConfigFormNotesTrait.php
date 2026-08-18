<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Integration;

use MailVotech\IntegrationsBundle\DTO\Note;

trait ConfigFormNotesTrait
{
    public function getAuthorizationNote(): ?Note
    {
        return null;
    }

    public function getFeaturesNote(): ?Note
    {
        return null;
    }

    public function getFieldMappingNote(): ?Note
    {
        return null;
    }
}
