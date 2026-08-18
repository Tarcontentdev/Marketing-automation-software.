<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechFocusBundle\Tests\Functional\Controller;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\ProjectBundle\Entity\Project;
use MailVotechPlugin\MailVotechFocusBundle\Entity\Focus;

final class FocusControllerTest extends MailVotechMysqlTestCase
{
    public function testFocusWithProject(): void
    {
        $focus = new Focus();
        $focus->setName('Test Focus');
        $focus->setType('notice');
        $focus->setStyle('bar');
        $this->em->persist($focus);

        $project = new Project();
        $project->setName('Test Project');
        $this->em->persist($project);

        $this->em->flush();
        $this->em->clear();

        $crawler = $this->client->request('GET', '/s/focus/edit/'.$focus->getId());
        $form    = $crawler->selectButton('Save')->form();
        $form['focus[projects]']->setValue((string) $project->getId());

        $this->client->submit($form);

        $this->assertResponseIsSuccessful();

        $savedFocus = $this->em->find(Focus::class, $focus->getId());
        $this->assertInstanceOf(Focus::class, $savedFocus);
        $this->assertSame($project->getId(), $savedFocus->getProjects()->first()->getId());
    }
}
