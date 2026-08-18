<?php

declare(strict_types=1);

namespace MailVotech\FormBundle\Collector;

use MailVotech\FormBundle\Collection\MappedObjectCollection;

interface MappedObjectCollectorInterface
{
    public function buildCollection(string ...$objects): MappedObjectCollection;
}
