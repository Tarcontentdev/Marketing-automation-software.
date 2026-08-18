<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Event;

use MailVotech\LeadBundle\Entity\ContactExportScheduler;
use Symfony\Contracts\EventDispatcher\Event;

final class ContactExportSchedulerEvent extends Event
{
    private string $filePath;

    public function __construct(
        private readonly ContactExportScheduler $contactExportScheduler,
    ) {
    }

    public function getContactExportScheduler(): ContactExportScheduler
    {
        return $this->contactExportScheduler;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): void
    {
        $this->filePath = $filePath;
    }
}
