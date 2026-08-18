<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Tests\Functional\Helper;

use MailVotech\CoreBundle\Helper\Chart\ChartQuery;
use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\LeadBundle\Entity\Lead;

final class ChartQueryFunctionalTest extends MailVotechMysqlTestCase
{
    public function testGetCountQueryWithUniqueOptionsExecutesAgainstDatabase(): void
    {
        $lead = new Lead();
        $lead->setEmail('chart-query-functional@example.test');
        $lead->setDateAdded(new \DateTime('now', new \DateTimeZone('UTC')));
        $lead->setDateIdentified(new \DateTime('now', new \DateTimeZone('UTC')));
        $this->em->persist($lead);
        $this->em->flush();

        $chartQuery = new ChartQuery(
            $this->connection,
            new \DateTime('-1 day', new \DateTimeZone('UTC')),
            new \DateTime('+1 day', new \DateTimeZone('UTC')),
            'd'
        );

        $countQuery = $chartQuery->getCountQuery(
            'leads',
            'id',
            null,
            [],
            ['getUnique' => true]
        );

        $count = $chartQuery->fetchCount($countQuery);

        $this->assertGreaterThanOrEqual(1, $count, 'Expected at least one unique lead in the date range.');
    }
}
