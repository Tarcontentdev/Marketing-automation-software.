<?php

declare(strict_types=1);

namespace MailVotech\FormBundle\Collector;

use MailVotech\FormBundle\Collection\FieldCollection;

interface FieldCollectorInterface
{
    public function getFields(string $object): FieldCollection;
}
