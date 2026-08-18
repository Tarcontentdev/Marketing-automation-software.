<?php

declare(strict_types=1);

namespace Utils\PHPStan\Tests\Rule\Fixture;

final class DefineOtherConstantTest
{
    public function setUp(): void
    {
        defined('MAILVOTECH_ENV') or define('MAILVOTECH_ENV', 'test');
    }
}
