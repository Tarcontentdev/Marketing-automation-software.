<?php

declare(strict_types=1);

namespace MailVotech\WebhookBundle\Tests\Form\Type;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use PHPUnit\Framework\Assert;

final class ConfigTypeFunctionalTest extends MailVotechMysqlTestCase
{
    public function testSendEmailDetailsToggleIsOnByDefault(): void
    {
        $crawler = $this->client->request('GET', '/s/config/edit');

        // Updated CSS selector based on the new ID
        $yesSpan = $crawler->filter('#config_webhookconfig_webhook_email_details_label > div > span');

        // Assert that exactly one such span exists
        $this->assertCount(1, $yesSpan, 'The "Yes" span for "Send email details" toggle should exist.');

        // Assert that the text within the span is "Yes"
        $this->assertSame('Yes', $yesSpan->text(), 'The "Send email details" toggle should be set to "Yes" by default.');
    }
}
