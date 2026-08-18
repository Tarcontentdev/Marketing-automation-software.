<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Tests\Functional\Helper;

use MailVotech\CoreBundle\Helper\LanguageHelper;
use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use PHPUnit\Framework\Assert;

final class LanguageHelperTest extends MailVotechMysqlTestCase
{
    public function testGettingLanguageFiles(): void
    {
        $languageHelper = self::getContainer()->get(LanguageHelper::class);
        $this->assertInstanceOf(LanguageHelper::class, $languageHelper);

        $languageFiles = $languageHelper->getLanguageFiles();

        // As the list depends on installed plugins, let's assert only for random files that should exist.
        $this->assertBundleContainsDefaultLanguageFile($languageFiles, 'EmailBundle');
        $this->assertBundleContainsDefaultLanguageFile($languageFiles, 'LeadBundle');
    }

    /**
     * @param array<string, string[]> $languageFiles
     */
    private function assertBundleContainsDefaultLanguageFile(array $languageFiles, string $bundle): void
    {
        $this->assertArrayHasKey($bundle, $languageFiles);
        $this->assertNotEmpty(array_filter(
            $languageFiles[$bundle],
            static fn (string $file): bool => 1 === preg_match(
                sprintf('/app\/bundles\/%s\/Translations\/en_US\/(messages|validators|flashes)\.ini/', $bundle),
                $file
            )
        ));
    }
}
