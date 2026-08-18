<?php

declare(strict_types=1);

namespace MailVotech\FormBundle\Tests\Crate;

use MailVotech\FormBundle\Crate\ObjectCrate;

final class ObjectCrateTest extends \PHPUnit\Framework\TestCase
{
    public function testGetters(): void
    {
        $field = new ObjectCrate('contact', 'Contact');

        $this->assertSame('contact', $field->getKey());
        $this->assertSame('Contact', $field->getName());
    }
}
