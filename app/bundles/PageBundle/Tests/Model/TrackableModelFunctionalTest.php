<?php

declare(strict_types=1);

namespace MailVotech\PageBundle\Tests\Model;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\PageBundle\Model\TrackableModel;
use PHPUnit\Framework\Attributes\DataProvider;

final class TrackableModelFunctionalTest extends MailVotechMysqlTestCase
{
    private TrackableModel $trackableModel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->trackableModel = self::getContainer()->get(TrackableModel::class);
    }

    /**
     * @param string[] $expectedTrackedUrls
     * @param string[] $expectedUntrackedHtml
     */
    #[DataProvider('disableTrackingDataProvider')]
    public function testDisableTrackingAttributeWorks(string $content, array $expectedTrackedUrls, array $expectedUntrackedHtml): void
    {
        [$newContent, $trackableTokens] = $this->trackableModel->parseContentForTrackables($content, [], 'email', 1);

        $this->assertCount(count($expectedTrackedUrls), $trackableTokens);

        $trackableUrls = [];
        foreach ($trackableTokens as $trackable) {
            $trackableUrls[] = $trackable->getRedirect()->getUrl();
        }

        // Sort both arrays to avoid issues with order.
        sort($expectedTrackedUrls);
        sort($trackableUrls);

        $this->assertSame($expectedTrackedUrls, $trackableUrls);

        foreach ($expectedUntrackedHtml as $untrackedHtml) {
            $this->assertStringContainsString($untrackedHtml, (string) $newContent);
        }

        if ([] === $expectedTrackedUrls) {
            $this->assertStringNotContainsString('{trackable=', (string) $newContent);
        } else {
            $this->assertStringContainsString('{trackable=', (string) $newContent);
        }
    }

    /**
     * @return iterable<string, array<int, string|string[]>>
     */
    public static function disableTrackingDataProvider(): iterable
    {
        yield 'HTML5 data attribute' => [
            <<<HTML
<a href="https://mailvotech.org">MailVotech</a>
<a href="https://google.com" data-mailvotech-disable-tracking="true">Google</a>
<a href="https://another-link.com">Another Link</a>
HTML,
            ['https://mailvotech.org', 'https://another-link.com'],
            ['<a href="https://google.com" data-mailvotech-disable-tracking="true">Google</a>'],
        ];

        yield 'HTML5 data attribute with false value is still tracked' => [
            <<<HTML
<a href="https://mailvotech.org">MailVotech</a>
<a href="https://google.com" data-mailvotech-disable-tracking="false">Google</a>
<a href="https://another-link.com">Another Link</a>
HTML,
            ['https://mailvotech.org', 'https://google.com', 'https://another-link.com'],
            [],
        ];

        yield 'Deprecated attribute' => [
            <<<HTML
<a href="https://mailvotech.org">MailVotech</a>
<a href="https://deprecated.com" mailvotech:disable-tracking>Deprecated</a>
HTML,
            ['https://mailvotech.org'],
            ['<a href="https://deprecated.com" mailvotech:disable-tracking>Deprecated</a>'],
        ];

        yield 'Both attributes on same link' => [
            <<<HTML
<a href="https://mailvotech.org">MailVotech</a>
<a href="https://google.com" data-mailvotech-disable-tracking="true" mailvotech:disable-tracking>Google</a>
HTML,
            ['https://mailvotech.org'],
            ['<a href="https://google.com" data-mailvotech-disable-tracking="true" mailvotech:disable-tracking>Google</a>'],
        ];

        yield 'Both attributes on different links' => [
            <<<HTML
<a href="https://mailvotech.org">MailVotech</a>
<a href="https://google.com" data-mailvotech-disable-tracking="true">Google</a>
<a href="https://deprecated.com" mailvotech:disable-tracking>Deprecated</a>
HTML,
            ['https://mailvotech.org'],
            [
                '<a href="https://google.com" data-mailvotech-disable-tracking="true">Google</a>',
                '<a href="https://deprecated.com" mailvotech:disable-tracking>Deprecated</a>',
            ],
        ];

        yield 'No tracking disabled' => [
            <<<HTML
<a href="https://mailvotech.org">MailVotech</a>
<a href="https://google.com">Google</a>
HTML,
            ['https://mailvotech.org', 'https://google.com'],
            [],
        ];
    }
}
