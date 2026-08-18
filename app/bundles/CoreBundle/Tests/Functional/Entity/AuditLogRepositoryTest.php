<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Tests\Functional\Entity;

use MailVotech\CoreBundle\Entity\AuditLog;
use MailVotech\CoreBundle\Entity\AuditLogRepository;
use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\LeadBundle\Entity\DoNotContact;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Model\LeadModel;
use PHPUnit\Framework\Attributes\DataProvider;

final class AuditLogRepositoryTest extends MailVotechMysqlTestCase
{
    /**
     * @param mixed[] $filters
     */
    #[DataProvider('dataForGetAuditLogsForLeads')]
    public function testGetAuditLogsForSingleLead(array $filters, int $expectedCount): void
    {
        /** @var LeadModel $contactModel */
        $contactModel = self::getContainer()->get(LeadModel::class);

        $contact = new Lead();
        $contact->setEmail('john@doe.com');

        $contactModel->saveEntity($contact);

        $contact->setFirstname('John')->setLastname('Doe');

        $contactModel->saveEntity($contact);

        $doNotContact = new DoNotContact();
        $doNotContact->setDateAdded(new \DateTime());
        $doNotContact->setChannel('channelName');
        $doNotContact->setChannelId(1);

        $this->em->persist($doNotContact);
        $contact->addDoNotContactEntry($doNotContact);

        /** @var AuditLogRepository $alRepo */
        $alRepo = $this->em->getRepository(AuditLog::class);

        $this->assertCount($expectedCount, $alRepo->getAuditLogsForLeads([$contact->getId()], $filters));
    }

    /**
     * @return iterable<string, mixed>
     */
    public static function dataForGetAuditLogsForLeads(): iterable
    {
        yield 'No filters' => [
            [],
            3,
        ];

        yield 'Filter: search' => [
            [
                'search' => 'john@doe.com',
            ],
            1,
        ];

        yield 'Filter: search for random text' => [
            [
                'search' => 'random text',
            ],
            0,
        ];

        yield 'Filter: includeEvents' => [
            [
                'includeEvents' => ['identified', 'create'],
            ],
            2,
        ];

        yield 'Filter: excludeEvents' => [
            [
                'excludeEvents' => ['identified', 'create'],
            ],
            1,
        ];
    }
}
