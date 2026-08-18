<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Event;

use MailVotech\LeadBundle\Entity\Company;
use Symfony\Contracts\EventDispatcher\Event;

final class InternalCompanyEvent extends Event
{
    public function __construct(
        private readonly string $integrationName,
        private readonly Company $company,
    ) {
    }

    public function getIntegrationName(): string
    {
        return $this->integrationName;
    }

    public function getCompany(): Company
    {
        return $this->company;
    }
}
