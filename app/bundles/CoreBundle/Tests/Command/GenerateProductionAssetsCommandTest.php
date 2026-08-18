<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Tests\Command;

use MailVotech\CoreBundle\Helper\Filesystem;
use MailVotech\CoreBundle\Helper\PathsHelper;
use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;

final class GenerateProductionAssetsCommandTest extends MailVotechMysqlTestCase
{
    private const CKEDITOR_FILE_NAME      = 'ckeditor.js';

    private const TEMP_CKEDITOR_FILE_NAME = 'temp_ckeditor.js';

    private Filesystem $filesystem;

    private string $ckeditorFilePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filesystem = self::getContainer()->get(Filesystem::class);
        /** @var PathsHelper $pathHelper */
        $pathHelper       = self::getContainer()->get(PathsHelper::class);

        $this->ckeditorFilePath = $pathHelper->getVendorRootPath().'/media/libraries/ckeditor/';
    }

    public function testAssetGenerateCommand(): void
    {
        $commandTester = $this->testSymfonyCommand('mailvotech:assets:generate');
        $this->assertStringContainsString('Production assets have been regenerated.', $commandTester->getDisplay());
        $this->assertSame(0, $commandTester->getStatusCode());
    }

    public function testCkeditorFileNotExist(): void
    {
        $ckeditorFilePath = $this->ckeditorFilePath.self::CKEDITOR_FILE_NAME;
        if ($this->filesystem->exists($ckeditorFilePath)) {
            $this->filesystem->rename($ckeditorFilePath, $this->ckeditorFilePath.self::TEMP_CKEDITOR_FILE_NAME);
        }

        $commandTester = $this->testSymfonyCommand('mailvotech:assets:generate');
        $this->assertStringContainsString("{$ckeditorFilePath} does not exist. Execute `npm install` to generate it.", $commandTester->getDisplay());
        $this->assertSame(1, $commandTester->getStatusCode());
    }

    protected function beforeTearDown(): void
    {
        if ($this->filesystem->exists($this->ckeditorFilePath.self::TEMP_CKEDITOR_FILE_NAME)) {
            $this->filesystem->rename($this->ckeditorFilePath.self::TEMP_CKEDITOR_FILE_NAME, $this->ckeditorFilePath.self::CKEDITOR_FILE_NAME);
        }
    }
}
