<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\Controller;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;

final class LeadCompanyControllerTest extends MailVotechMysqlTestCase
{
    protected function setUp(): void
    {
        $this->configParams['contact_allow_multiple_companies']   = 0;
        parent::setUp();
    }

    public function testSimpleCompanyFeature(): void
    {
        $crawler     = $this->client->request('GET', 's/contacts/new/');
        $multiple    = $crawler->filterXPath('//*[@id="lead_companies"]')->attr('multiple');
        $this->assertNull($multiple);
    }
}
