<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\Command;

use MailVotech\CoreBundle\Helper\DateTimeHelper;
use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\CoreBundle\Test\ReflectionHelper;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Entity\LeadList;
use MailVotech\LeadBundle\Entity\LeadListRepository;
use MailVotech\LeadBundle\Entity\LeadRepository;
use MailVotech\LeadBundle\Entity\ListLead;
use PHPUnit\Framework\Attributes\DataProvider;

final class SegmentFilterWithRelativeTimeFunctionalTest extends MailVotechMysqlTestCase
{
    #[DataProvider('getRelativeHours')]
    public function testSegmentFilterWithRelativeTime(int $hours): void
    {
        $this->saveContacts();
        $segment = $this->saveSegment($hours);

        $this->testSymfonyCommand('mailvotech:segments:update', ['-i' => $segment->getId()]);
        $this->assertCount($hours, $this->em->getRepository(ListLead::class)->findBy(['list' => $segment->getId()]));
    }

    /**
     * @return Lead[]
     */
    private function saveContacts(): array
    {
        // Add 10 contacts
        /** @var LeadRepository $contactRepo */
        $contactRepo = $this->em->getRepository(Lead::class);
        /** @var Lead[] $contacts */
        $contacts    = [];

        for ($i = 1; $i <= 10; ++$i) {
            $contact = new Lead();
            $contact->setFirstname('fn'.$i);
            $contact->setLastname('ln'.$i);
            $contact->setLastActive(new \DateTime(sprintf('-%s hours +15 seconds', $i)));
            $contacts[] = $contact;
        }

        $contactRepo->saveEntities($contacts);

        return $contacts;
    }

    private function saveSegment(int $hours): LeadList
    {
        /** @var LeadListRepository $segmentRepo */
        $segmentRepo = $this->em->getRepository(LeadList::class);
        $segment     = new LeadList();
        $filters     = [
            [
                'object'     => 'lead',
                'glue'       => 'and',
                'field'      => 'last_active',
                'type'       => 'datetime',
                'operator'   => 'gte',
                'properties' => ['filter' => sprintf('-%s hours', $hours)],
            ],
        ];

        $segment->setName('Segment')
            ->setPublicName('Segment')
            ->setFilters($filters)
            ->setAlias('segment');
        $segmentRepo->saveEntity($segment);

        return $segment;
    }

    public function testSegmentFilterWithRelativeTimeAndNonUtcTimezone(): void
    {
        /** @var LeadRepository $contactRepo */
        $contactRepo = $this->em->getRepository(Lead::class);
        $contact     = new Lead();
        $contact->setFirstname('timezone');
        $contact->setLastname('test');
        $contact->setLastActive(new \DateTime('-30 minutes', new \DateTimeZone('UTC')));
        $contactRepo->saveEntity($contact);

        $segment = $this->saveSegment(1); // filter: last_active >= -1 hour

        $tzProperty             = new \ReflectionProperty(DateTimeHelper::class, 'defaultLocalTimezone');
        $originalCachedTimezone = $tzProperty->getValue();
        ReflectionHelper::setStaticValue(DateTimeHelper::class, 'defaultLocalTimezone', 'Europe/Prague');

        try {
            $this->testSymfonyCommand('mailvotech:segments:update', ['-i' => $segment->getId()]);

            $this->assertCount(1, $this->em->getRepository(ListLead::class)->findBy(['list' => $segment->getId()]), 'Contact last active 30 min ago must be included in the "-1 hour" segment even when the system timezone is non-UTC.');
        } finally {
            ReflectionHelper::setStaticValue(DateTimeHelper::class, 'defaultLocalTimezone', $originalCachedTimezone);
        }
    }

    /**
     * @return iterable<int[]>
     */
    public static function getRelativeHours(): iterable
    {
        yield [1];
        yield [3];
        yield [5];
    }
}
