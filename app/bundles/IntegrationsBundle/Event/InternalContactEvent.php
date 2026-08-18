<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Event;

use MailVotech\LeadBundle\Entity\Lead;
use Symfony\Contracts\EventDispatcher\Event;

final class InternalContactEvent extends Event
{
    public function __construct(
        private readonly string $integrationName,
        private readonly Lead $contact,
    ) {
    }

    public function getIntegrationName(): string
    {
        return $this->integrationName;
    }

    public function getContact(): Lead
    {
        return $this->contact;
    }
}
