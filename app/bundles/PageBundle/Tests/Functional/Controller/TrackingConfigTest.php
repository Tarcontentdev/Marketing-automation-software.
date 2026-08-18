<?php

declare(strict_types=1);

namespace MailVotech\PageBundle\Tests\Functional\Controller;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Request;

final class TrackingConfigTest extends MailVotechMysqlTestCase
{
    public function testTrackingScriptOptionsAreRendered(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/s/config/edit?tab=trackingconfig');

        self::assertResponseIsSuccessful();

        $getSnippet = static function (string $ariaLabel) use ($crawler): string {
            $snippet = $crawler->filter(sprintf('pre[aria-label="%s"]', $ariaLabel));
            Assert::assertCount(1, $snippet);

            return $snippet->text();
        };

        $essential = $getSnippet('Essential script (before consent)');
        $this->assertStringContainsString('/mailvotech-essential.js', $essential);
        $this->assertStringContainsString("dispatchEvent('mailvotechEssentialReady')", $essential);
        $this->assertStringNotContainsString('/mailvotech-tracking.js', $essential);
        $this->assertStringNotContainsString('/mtc.js', $essential);
        $this->assertStringNotContainsString('MailVotechTrackingObject', $essential);
        $this->assertStringNotContainsString('pageview', $essential);

        $tracking = $getSnippet('Tracking add-on (after consent)');
        $this->assertStringContainsString('/mailvotech-tracking.js', $tracking);
        $this->assertStringContainsString("d.addEventListener('mailvotechEssentialReady',enableTracking)", $tracking);
        $this->assertStringContainsString('w.MailVotechJS.runtimeReady !== true', $tracking);
        $this->assertStringContainsString("w['MailVotechTrackingObject']=n", $tracking);
        $this->assertStringContainsString("w[n]('send','pageview')", $tracking);
        $this->assertStringContainsString("a.id='mailvotech-tracking-script'", $tracking);
        $this->assertStringContainsString("d.getElementById('mailvotech-tracking-script')", $tracking);
        $this->assertStringNotContainsString('/mailvotech-essential.js', $tracking);
        $this->assertStringNotContainsString('/mtc.js', $tracking);

        $full = $getSnippet('Full tracking');
        $this->assertSame(1, substr_count($full, '/mailvotech-essential.js'));
        $this->assertSame(1, substr_count($full, '/mailvotech-tracking.js'));
        $this->assertStringNotContainsString('/mtc.js', $full);
        $this->assertStringContainsString('a.onload=function()', $full);
        $this->assertStringContainsString('s.src=r', $full);
        $this->assertStringContainsString("mt('send', 'pageview');", $full);

        $essentialPosition = strpos($full, '/mailvotech-essential.js');
        $trackingPosition  = strpos($full, '/mailvotech-tracking.js');
        $this->assertNotFalse($essentialPosition);
        $this->assertNotFalse($trackingPosition);
        $this->assertLessThan($trackingPosition, $essentialPosition);

        $this->assertCount(0, $crawler->filter('pre:contains("/mtc.js")'));
    }
}
