<?php

declare(strict_types=1);

namespace MailVotech\FormBundle\Tests\Collection;

use MailVotech\FormBundle\Collection\ObjectCollection;
use MailVotech\FormBundle\Crate\ObjectCrate;

final class ObjectCollectionTest extends \PHPUnit\Framework\TestCase
{
    public function testToChoicesWithObjects(): void
    {
        $collection = new ObjectCollection(
            [
                new ObjectCrate('contact', 'Contact'),
                new ObjectCrate('company', 'Company'),
            ]
        );

        $this->assertSame(
            [
                'Contact' => 'contact',
                'Company' => 'company',
            ],
            $collection->toChoices()
        );
    }

    public function testToChoicesWithoutObjects(): void
    {
        $collection = new ObjectCollection();

        $this->assertSame([], $collection->toChoices());
    }
}
