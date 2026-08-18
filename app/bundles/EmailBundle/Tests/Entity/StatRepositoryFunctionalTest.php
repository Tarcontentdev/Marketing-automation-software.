<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\Tests\Entity;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\EmailBundle\Entity\Email;
use MailVotech\EmailBundle\Entity\Stat;
use MailVotech\EmailBundle\Entity\StatRepository;

final class StatRepositoryFunctionalTest extends MailVotechMysqlTestCase
{
    private StatRepository $statRepository;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var StatRepository $repository */
        $repository           = $this->em->getRepository(Stat::class);
        $this->statRepository = $repository;
    }

    public function testGetEmailSentLastDateIncludesVariantParentStats(): void
    {
        $parentEmail = $this->createEmail('Parent email', 'Parent subject');
        $childEmail  = $this->createEmail('Child email', 'Child subject');
        $childEmail->setVariantParent($parentEmail);

        $this->createStat($parentEmail, '2026-03-10 09:00:00');
        $this->createStat($childEmail, '2026-03-20 09:00:00');
        $this->em->flush();

        $this->assertSame('2026-03-20 09:00:00', $this->statRepository->getEmailSentLastDate((int) $parentEmail->getId()));
    }

    public function testGetEmailSentLastDateReturnsNullWithoutStats(): void
    {
        $email = $this->createEmail('No stats email', 'No stats subject');
        $this->em->flush();

        $this->assertNull($this->statRepository->getEmailSentLastDate((int) $email->getId()));
    }

    private function createEmail(string $name, string $subject): Email
    {
        $email = new Email();
        $email->setName($name);
        $email->setSubject($subject);
        $email->setEmailType('list');
        $this->em->persist($email);

        return $email;
    }

    private function createStat(Email $email, string $dateSent): void
    {
        $stat = new Stat();
        $stat->setEmail($email);
        $stat->setEmailAddress('contact@example.test');
        $stat->setDateSent(new \DateTime($dateSent, new \DateTimeZone('UTC')));
        $this->em->persist($stat);
    }
}
