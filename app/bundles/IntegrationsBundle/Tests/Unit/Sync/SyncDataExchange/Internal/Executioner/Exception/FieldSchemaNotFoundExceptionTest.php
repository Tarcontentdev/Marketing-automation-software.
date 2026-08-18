<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Tests\Unit\Sync\SyncDataExchange\Internal\Executioner\Exception;

use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Internal\Executioner\Exception\FieldSchemaNotFoundException;
use PHPUnit\Framework\TestCase;

final class FieldSchemaNotFoundExceptionTest extends TestCase
{
    public function testMessage(): void
    {
        $object    = 'SomeObject';
        $alias     = 'SomeAlias';
        $exception = new FieldSchemaNotFoundException($object, $alias);
        $expected  = sprintf('Schema for alias "%s" of object "%s" not found', $alias, $object);
        $this->assertSame($expected, $exception->getMessage());
    }
}
