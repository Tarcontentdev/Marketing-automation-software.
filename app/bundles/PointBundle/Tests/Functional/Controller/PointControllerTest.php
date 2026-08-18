<?php

declare(strict_types=1);

namespace MailVotech\PointBundle\Tests\Functional\Controller;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\PointBundle\Entity\Point;
use MailVotech\ProjectBundle\Entity\Project;

final class PointControllerTest extends MailVotechMysqlTestCase
{
    public function testPointWithProject(): void
    {
        $point = new Point();
        $point->setName('test');
        $point->setType('url.hit');
        $this->em->persist($point);

        $project = new Project();
        $project->setName('Test Project');
        $this->em->persist($project);

        $this->em->flush();
        $this->em->clear();

        $crawler = $this->client->request('GET', '/s/points/edit/'.$point->getId());
        $form    = $crawler->selectButton('Save')->form();
        $form['point[projects]']->setValue((string) $project->getId());

        $this->client->submit($form);

        $this->assertResponseIsSuccessful();

        $savedAsset = $this->em->find(Point::class, $point->getId());
        $this->assertInstanceOf(Point::class, $savedAsset);
        $this->assertSame($project->getId(), $savedAsset->getProjects()->first()->getId());
    }
}
