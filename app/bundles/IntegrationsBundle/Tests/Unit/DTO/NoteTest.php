<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Tests\Unit\DTO;

use MailVotech\IntegrationsBundle\DTO\Note;
use PHPUnit\Framework\TestCase;

final class NoteTest extends TestCase
{
    public function testGetterFunctions(): void
    {
        $note = 'This is note';
        $type = Note::TYPE_WARNING;

        $noteObject = new Note($note, $type);

        $this->assertSame($note, $noteObject->getNote());
        $this->assertSame($type, $noteObject->getType());
    }

    public function testGetterFunctionsThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Type value can be either "%s" or "%s".', Note::TYPE_INFO, Note::TYPE_WARNING));

        new Note('Notes', 'randomType');
    }
}
