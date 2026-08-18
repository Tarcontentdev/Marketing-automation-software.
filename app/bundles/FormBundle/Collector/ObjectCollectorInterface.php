<?php

declare(strict_types=1);

namespace MailVotech\FormBundle\Collector;

use MailVotech\FormBundle\Collection\ObjectCollection;

interface ObjectCollectorInterface
{
    public function getObjects(): ObjectCollection;
}
