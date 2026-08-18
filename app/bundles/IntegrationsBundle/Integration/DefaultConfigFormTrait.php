<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Integration;

trait DefaultConfigFormTrait
{
    /**
     * Use the default.
     */
    public function getConfigFormName(): ?string
    {
        return null;
    }

    /**
     * Use the default.
     */
    public function getConfigFormContentTemplate(): ?string
    {
        return null;
    }

    /**
     * Use the default.
     */
    public function getSyncConfigFormName(): ?string
    {
        return null;
    }
}
