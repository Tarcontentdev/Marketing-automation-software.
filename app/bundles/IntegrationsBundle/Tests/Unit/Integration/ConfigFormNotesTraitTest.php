<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Tests\Unit\Integration;

use MailVotech\IntegrationsBundle\DTO\Note;
use MailVotech\IntegrationsBundle\Integration\ConfigFormNotesTrait;
use MailVotech\IntegrationsBundle\Integration\Interfaces\ConfigFormNotesInterface;
use PHPUnit\Framework\TestCase;

final class ConfigFormNotesTraitTest extends TestCase
{
    public function testConfigFormNotesTraitFormDefaultValues(): void
    {
        $configFormNotes = new class() implements ConfigFormNotesInterface {
            use ConfigFormNotesTrait;
        };

        $this->assertNotInstanceOf(Note::class, $configFormNotes->getAuthorizationNote());
        $this->assertNotInstanceOf(Note::class, $configFormNotes->getFeaturesNote());
        $this->assertNotInstanceOf(Note::class, $configFormNotes->getFieldMappingNote());
    }

    public function testConfigFormNotesTraitFormForCustomValues(): void
    {
        $configFormNotes = new class() implements ConfigFormNotesInterface {
            use ConfigFormNotesTrait;

            public function getAuthorizationNote(): Note
            {
                return new Note('Authorisation', Note::TYPE_WARNING);
            }

            public function getFeaturesNote(): Note
            {
                return new Note('Features', Note::TYPE_INFO);
            }

            public function getFieldMappingNote(): Note
            {
                return new Note('Field Mapping', Note::TYPE_WARNING);
            }
        };

        $this->assertSame(Note::TYPE_WARNING, $configFormNotes->getAuthorizationNote()->getType());
        $this->assertSame('Authorisation', $configFormNotes->getAuthorizationNote()->getNote());

        $this->assertSame(Note::TYPE_INFO, $configFormNotes->getFeaturesNote()->getType());
        $this->assertSame('Features', $configFormNotes->getFeaturesNote()->getNote());

        $this->assertSame(Note::TYPE_WARNING, $configFormNotes->getFieldMappingNote()->getType());
        $this->assertSame('Field Mapping', $configFormNotes->getFieldMappingNote()->getNote());
    }
}
