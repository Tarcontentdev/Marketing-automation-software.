<?php

declare(strict_types=1);

namespace MailVotech\FormBundle\Event;

use MailVotech\FormBundle\Collection\FieldCollection;
use MailVotech\FormBundle\Crate\FieldCrate;
use Symfony\Contracts\EventDispatcher\Event;

final class FieldCollectEvent extends Event
{
    private readonly FieldCollection $fields;

    public function __construct(
        private readonly string $object,
    ) {
        $this->fields = new FieldCollection();
    }

    public function getObject(): string
    {
        return $this->object;
    }

    public function appendField(FieldCrate $field): void
    {
        $this->fields->append($field);
    }

    public function getFields(): FieldCollection
    {
        return $this->fields;
    }
}
