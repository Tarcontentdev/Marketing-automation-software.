<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\Tests\Functional;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\EmailBundle\Entity\Email;
use MailVotech\EmailBundle\Entity\Stat;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\PageBundle\Entity\Hit;
use MailVotech\PageBundle\Entity\HitRepository;
use MailVotech\PageBundle\Entity\Page;
use Symfony\Component\HttpFoundation\Request;

final class EmailClickTrackingTest extends MailVotechMysqlTestCase
{
    public function testEmailClick(): void
    {
        $contact = new Lead();
        $contact->setEmail('john@doe.cz');

        $email = new Email();
        $email->setName('Test email');
        $email->setSubject('Test email');
        $email->setCustomHtml('<html><head></head><body>Test email</body></html>');

        $stat = new Stat();
        $stat->setLead($contact);
        $stat->setEmail($email);
        $stat->setEmailAddress('john@doe.cz');
        $stat->setTrackingHash('67167f57a4c05265936091');
        $stat->setDateSent(new \DateTime());

        $page = new Page();
        $page->setTitle('Test page');
        $page->setAlias('test-page');
        $page->setCustomHtml('<html><head></head><body>Test page</body></html>');

        $this->em->persist($contact);
        $this->em->persist($email);
        $this->em->persist($stat);
        $this->em->persist($page);
        $this->em->flush();

        $this->logoutUser();

        $this->client->request(Request::METHOD_GET, '/test-page?&ct=YToxOntzOjQ6InN0YXQiO3M6MjI6IjY3MTY3ZjU3YTRjMDUyNjU5MzYwOTEiO30%3D');
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $pageHitRepository = $this->em->getRepository(Hit::class);
        $this->assertInstanceOf(HitRepository::class, $pageHitRepository);

        $hit = $pageHitRepository->findOneBy(['page' => $page]);
        $this->assertInstanceOf(Hit::class, $hit);
        $this->assertSame($contact->getId(), $hit->getLead()->getId());
    }
}
