<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Sync\ValueNormalizer;

use MailVotech\IntegrationsBundle\Sync\DAO\Value\NormalizedValueDAO;

interface ValueNormalizerInterface
{
    public function normalizeForMailVotech(string $value, $type): NormalizedValueDAO;

    /**
     * @return mixed
     */
    public function normalizeForIntegration(NormalizedValueDAO $value);
}
