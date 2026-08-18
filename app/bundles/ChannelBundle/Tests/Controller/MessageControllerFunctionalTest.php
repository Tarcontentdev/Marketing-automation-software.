<?php

declare(strict_types=1);

namespace MailVotech\ChannelBundle\Tests\Controller;

use MailVotech\ChannelBundle\Entity\Message;
use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\ProjectBundle\Entity\Project;

final class MessageControllerFunctionalTest extends MailVotechMysqlTestCase
{
    public function testFormWithProject(): void
    {
        $message = new Message();
        $message->setName('Test message');
        $this->em->persist($message);

        $project = new Project();
        $project->setName('Test Project');
        $this->em->persist($project);

        $this->em->flush();
        $this->em->clear();

        $crawler = $this->client->request('GET', '/s/messages/edit/'.$message->getId());
        $form    = $crawler->selectButton('Save')->form();
        $form['message[projects]']->setValue((string) $project->getId());

        $this->client->submit($form);

        $this->assertResponseIsSuccessful();

        $savedMessage = $this->em->find(Message::class, $message->getId());
        $this->assertInstanceOf(Message::class, $savedMessage);
        $this->assertSame($project->getId(), $savedMessage->getProjects()->first()->getId());
    }
}
