<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\Form\Type;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\EmailBundle\Entity\Copy;
use MailVotech\LeadBundle\Entity\Lead;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;

final class EmailTypeFunctionalTest extends MailVotechMysqlTestCase
{
    /**
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Exception
     */
    public function testEmailWithJapanese(): void
    {
        // New contact
        $lead = new Lead();
        $lead->setEmail('test@domain.tld');
        $this->em->persist($lead);
        $this->em->flush();

        // Fetch the form
        $this->client->request(Request::METHOD_GET, '/s/contacts/email/'.$lead->getId());
        $this->assertResponseIsSuccessful();
        $content     = $this->client->getResponse()->getContent();
        $content     = json_decode($content)->newContent;
        $crawler     = new Crawler($content, $this->client->getInternalRequest()->getUri());
        $formCrawler = $crawler->filter('form');
        $this->assertCount(1, $formCrawler);
        $form = $formCrawler->form();

        // Send email to contact
        $form->setValues([
            'lead_quickemail[fromname]' => 'Admin',
            'lead_quickemail[from]'     => 'admin@mailvotech.com',
            'lead_quickemail[subject]'  => 'Test Jap MailVotech',
            'lead_quickemail[body]'     => '<p style="font-family: メイリオ">Test</p>',
            'lead_quickemail[list]'     => 0,
        ]);
        $this->client->submit($form);
        $this->assertResponseIsSuccessful();

        // Check the email has correct text
        $copy = $this->em->getRepository(Copy::class)->findOneBy(['subject' => 'Test Jap MailVotech']);
        $this->assertInstanceOf(Copy::class, $copy);
        $this->assertStringContainsString('<p style="font-family: メイリオ">Test</p>', (string) $copy->getBody());
    }
}
