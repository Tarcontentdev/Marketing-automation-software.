<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\Command;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Entity\LeadList;
use MailVotech\LeadBundle\Entity\ListLead;

final class SegmentNumberFilterWithOrsCommandFunctionalTest extends MailVotechMysqlTestCase
{
    public function testSegmentNuberFilterWithOrsCommand(): void
    {
        $contact1 = new Lead();
        $contact1->setPoints(1);
        $contact2 = new Lead();
        $contact2->setPoints(2);
        $contact3 = new Lead();
        $contact3->setPoints(3);
        $contact4 = new Lead();
        $contact4->setPoints(4);

        $this->em->persist($contact1);
        $this->em->persist($contact2);
        $this->em->persist($contact3);
        $this->em->persist($contact4);

        $segment = new LeadList();
        $segment->setName('Segment A');
        $segment->setPublicName('Segment A');
        $segment->setAlias('segment-a');
        $segment->setFilters([
            [
                'object'     => 'lead',
                'glue'       => 'and',
                'field'      => 'points',
                'type'       => 'number',
                'operator'   => '=',
                'properties' => ['filter' => 1],
            ],
            [
                'object'     => 'lead',
                'glue'       => 'or',
                'field'      => 'points',
                'type'       => 'number',
                'operator'   => '=',
                'properties' => ['filter' => 2],
            ],
            [
                'object'     => 'lead',
                'glue'       => 'or',
                'field'      => 'points',
                'type'       => 'number',
                'operator'   => '=',
                'properties' => ['filter' => 3],
            ],
        ]);

        $this->em->persist($segment);
        $this->em->flush();

        $this->testSymfonyCommand('mailvotech:segments:update', ['-i' => $segment->getId()]);
        $this->assertCount(3, $this->em->getRepository(ListLead::class)->findBy(['list' => $segment]));
    }

    public function testSegmentNuberFilterWithRegexCommand(): void
    {
        $contact1 = new Lead();
        $contact1->setPoints(1);
        $contact2 = new Lead();
        $contact2->setPoints(2);
        $contact3 = new Lead();
        $contact3->setPoints(3);

        $this->em->persist($contact1);
        $this->em->persist($contact2);
        $this->em->persist($contact3);

        $segment = new LeadList();
        $segment->setName('Segment A');
        $segment->setPublicName('Segment A');
        $segment->setAlias('segment-a');
        $segment->setFilters([
            [
                'object'     => 'lead',
                'glue'       => 'and',
                'field'      => 'points',
                'type'       => 'number',
                'operator'   => 'regexp',
                'properties' => ['filter' => '^(1|3)$'],
            ],
        ]);

        $this->em->persist($segment);
        $this->em->flush();

        $this->testSymfonyCommand('mailvotech:segments:update', ['-i' => $segment->getId()]);
        $this->assertCount(2, $this->em->getRepository(ListLead::class)->findBy(['list' => $segment]));
    }
}
