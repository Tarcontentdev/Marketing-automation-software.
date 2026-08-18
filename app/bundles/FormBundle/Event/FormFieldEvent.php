<?php

declare(strict_types=1);

namespace MailVotech\FormBundle\Event;

use MailVotech\FormBundle\Entity\Field;
use Symfony\Contracts\EventDispatcher\Event;

final class FormFieldEvent extends Event
{
    public function __construct(
        private Field $entity,
        private readonly bool $isNew = false,
    ) {
    }

    public function getField(): Field
    {
        return $this->entity;
    }

    public function isNew(): bool
    {
        return $this->isNew;
    }

    public function setField(Field $field): void
    {
        $this->entity = $field;
    }
}
