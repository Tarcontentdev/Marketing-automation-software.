<?php

declare(strict_types=1);

namespace MailVotech\FormBundle\Event;

use MailVotech\CoreBundle\Event\CommonEvent;
use MailVotech\FormBundle\Collection\ObjectCollection;
use MailVotech\FormBundle\Crate\ObjectCrate;

final class ObjectCollectEvent extends CommonEvent
{
    private readonly ObjectCollection $objects;

    public function __construct()
    {
        $this->objects = new ObjectCollection();
    }

    public function appendObject(ObjectCrate $object): void
    {
        $this->objects->append($object);
    }

    public function getObjects(): ObjectCollection
    {
        return $this->objects;
    }
}
