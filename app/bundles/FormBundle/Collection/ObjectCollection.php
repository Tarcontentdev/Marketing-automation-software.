<?php

declare(strict_types=1);

namespace MailVotech\FormBundle\Collection;

use MailVotech\FormBundle\Crate\ObjectCrate;

/**
 * @extends \ArrayIterator<int,ObjectCrate>
 */
final class ObjectCollection extends \ArrayIterator
{
    /**
     * @return array<string,string>
     */
    public function toChoices(): array
    {
        $choices = [];

        /** @var ObjectCrate $object */
        foreach ($this as $object) {
            $choices[$object->getName()] = $object->getKey();
        }

        return $choices;
    }
}
