<?php

declare(strict_types=1);

namespace MailVotech\PageBundle\Tests\Controller;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\PageBundle\Entity\Hit;
use MailVotech\PageBundle\Entity\HitRepository;
use MailVotech\PageBundle\Entity\Page;
use Symfony\Component\HttpFoundation\Request;

final class VisitPageWitIpAnonymizationOnFunctionalTest extends MailVotechMysqlTestCase
{
    protected function setUp(): void
    {
        $this->configParams['anonymize_ip'] = true;

        parent::setUp();
    }

    public function testPageWithIpAnonymizationOn(): void
    {
        // create landing page
        $pageObject = new Page();
        $pageObject->setIsPublished(true);
        $pageObject->setDateAdded(new \DateTime());
        $pageObject->setTitle('Page:Page:Anonymization:On');
        $pageObject->setAlias('page-page-anonymizaiton-on');
        $pageObject->setTemplate('Blank');
        $pageObject->setCustomHtml('Test Html');
        $pageObject->setLanguage('en');
        $this->em->persist($pageObject);
        $this->em->flush();

        $this->logoutUser();
        $pageContent = $this->client->request(Request::METHOD_GET, '/page-page-anonymizaiton-on');

        self::assertResponseIsSuccessful();
        $this->assertStringContainsString('Test Html', $pageContent->text());

        /** @var HitRepository $hitRepository */
        $hitRepository = $this->em->getRepository(Hit::class);

        /** @var Hit[] $hits */
        $hits = $hitRepository->findBy(['page' => $pageObject->getId()]);
        $this->assertCount(1, $hits);
        $this->assertSame('*.*.*.*', $hits[0]->getIpAddress()->getIpAddress());
    }
}
