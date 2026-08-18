<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\Tests\Model;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\EmailBundle\Model\EmailModel;

final class EmailModelBuildUrlTest extends MailVotechMysqlTestCase
{
    protected function setUp(): void
    {
        $this->configParams['site_url'] = 'https://foo.bar.com';
        parent::setUp();
    }

    public function testSiteUrlAlwaysTakesPrecedenceWhenBuildingUrls(): void
    {
        /** @var EmailModel $emailModel */
        $emailModel = self::getContainer()->get(EmailModel::class);
        $idHash     = uniqid();
        $url        = $emailModel->buildUrl('mailvotech_email_unsubscribe', ['idHash' => $idHash]);

        $this->assertSame('https://foo.bar.com/email/unsubscribe/'.$idHash, $url);
    }
}
