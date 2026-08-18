<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Provider;

use MailVotech\LeadBundle\Exception\ChoicesNotFoundException;

interface FieldChoicesProviderInterface
{
    /**
     * @return mixed[]
     *
     * @throws ChoicesNotFoundException
     */
    public function getChoicesForField(string $fieldType, string $fieldAlias, string $search = ''): array;
}
