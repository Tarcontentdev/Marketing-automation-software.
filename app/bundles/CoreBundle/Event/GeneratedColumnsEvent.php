<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Event;

use MailVotech\CoreBundle\Doctrine\GeneratedColumn\GeneratedColumn;
use MailVotech\CoreBundle\Doctrine\GeneratedColumn\GeneratedColumns;
use Symfony\Contracts\EventDispatcher\Event;

final class GeneratedColumnsEvent extends Event
{
    private readonly GeneratedColumns $generatedColumns;

    public function __construct()
    {
        $this->generatedColumns = new GeneratedColumns();
    }

    public function getGeneratedColumns(): GeneratedColumns
    {
        return $this->generatedColumns;
    }

    public function addGeneratedColumn(GeneratedColumn $generatedColumn): void
    {
        $this->generatedColumns->add($generatedColumn);
    }
}
