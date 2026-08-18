<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Tests\Unit\ProcessSignal;

use MailVotech\CoreBundle\ProcessSignal\Exception\InvalidStateException;
use MailVotech\CoreBundle\ProcessSignal\ProcessSignalState;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ProcessSignalTest extends TestCase
{
    public function testGetData(): void
    {
        $data  = ['key' => 'value'];
        $state = new ProcessSignalState($data);

        $this->assertSame($data, $state->getData());
    }

    public function testToString(): void
    {
        $data  = ['key' => 'value'];
        $state = new ProcessSignalState($data);

        $this->assertSame('<<<StartOfState>>>{"key":"value"}<<<EndOfState>>>', (string) $state);
    }

    /**
     * @return iterable<string, string[]>
     */
    public static function dataFromStringThrowsException(): iterable
    {
        yield 'No tag' => ['No tag'];
        yield 'Invalid tag' => ['<<<StartOfState>>{"key":"value"}<<<EndOfState>>>'];
        yield 'Invalid JSON' => ['<<<StartOfState>>>{"key"="value"}<<<EndOfState>>>'];
    }

    #[DataProvider('datafromStringThrowsException')]
    public function testFromStringThrowsException(string $string): void
    {
        $this->expectException(InvalidStateException::class);
        ProcessSignalState::fromString($string);
    }
}
