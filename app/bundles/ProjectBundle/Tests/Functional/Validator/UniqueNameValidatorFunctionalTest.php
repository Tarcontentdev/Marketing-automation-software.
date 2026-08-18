<?php

declare(strict_types=1);

namespace MailVotech\ProjectBundle\Tests\Functional\Validator;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\ProjectBundle\Entity\Project;
use Symfony\Component\HttpFoundation\Request;

final class UniqueNameValidatorFunctionalTest extends MailVotechMysqlTestCase
{
    public function testDuplicateProjectName(): void
    {
        $project = new Project();
        $project->setName('qwerty');
        $this->em->persist($project);
        $this->em->flush();

        $this->assertCount(1, $this->em->getRepository(Project::class)->findBy(['name' => $project->getName()]));

        $crawler       = $this->client->request(Request::METHOD_GET, '/s/projects/new');
        $buttonCrawler = $crawler->selectButton('Save & Close');
        $form          = $buttonCrawler->form();
        $form['project_entity[name]']->setValue('QWERTY');
        $this->client->submit($form);
        $this->assertResponseIsSuccessful();

        $this->assertStringContainsString(
            'A project with this name already exists.',
            (string) $this->client->getResponse()->getContent()
        );

        $this->assertCount(1, $this->em->getRepository(Project::class)->findBy(['name' => $project->getName()]));
    }
}
