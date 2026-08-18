<?php

declare(strict_types=1);

namespace MailVotech\ChannelBundle\Tests\Controller;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use Symfony\Component\HttpFoundation\Request;

final class MessageControllerTest extends MailVotechMysqlTestCase
{
    public function testMMUiWorkflow(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/s/messages/new');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Save & Close')->form([
            'message[name]'        => 'Test message',
            'message[description]' => 'Test message description',
        ]);

        $this->client->submit($form);
        $this->assertResponseIsSuccessful();
    }
}
