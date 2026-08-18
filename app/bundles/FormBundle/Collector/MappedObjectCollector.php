<?php

declare(strict_types=1);

namespace MailVotech\FormBundle\Collector;

use MailVotech\FormBundle\Collection\MappedObjectCollection;

final readonly class MappedObjectCollector implements MappedObjectCollectorInterface
{
    public function __construct(
        private FieldCollectorInterface $fieldCollector,
    ) {
    }

    public function buildCollection(string ...$objects): MappedObjectCollection
    {
        $mappedObjectCollection = new MappedObjectCollection();

        foreach ($objects as $object) {
            if ($object) {
                $mappedObjectCollection->offsetSet($object, $this->fieldCollector->getFields($object));
            }
        }

        return $mappedObjectCollection;
    }
}
