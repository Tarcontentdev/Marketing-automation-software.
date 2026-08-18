<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Tests\Functional\Entity;

use MailVotech\CoreBundle\Entity\IpAddress;
use MailVotech\CoreBundle\Entity\IpAddressRepository;
use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;

final class CommonRepositoryUpsertTest extends MailVotechMysqlTestCase
{
    protected function beforeBeginTransaction(): void
    {
        $this->connection->executeStatement('ALTER TABLE '.MAILVOTECH_TABLE_PREFIX.'ip_addresses ADD UNIQUE INDEX idx_ip_address (ip_address)');
    }

    protected function afterRollback(): void
    {
        $this->connection->executeStatement('ALTER TABLE '.MAILVOTECH_TABLE_PREFIX.'ip_addresses DROP INDEX idx_ip_address');
    }

    public function testUpsert(): void
    {
        // Insert twice, to get two insert IDs, and then insert the first one again to trigger update, and check insert ID
        /** @var IpAddressRepository $ipAddressRepository */
        $ipAddressRepository = $this->getContainer()->get(IpAddressRepository::class);
        $ipAddress1          = new IpAddress('10.10.10.10');
        $ipAddressRepository->upsert($ipAddress1);
        $this->assertNotEmpty($ipAddress1->getId());
        $ipAddress2 = new IpAddress('10.10.10.11');
        $ipAddressRepository->upsert($ipAddress2);
        $this->assertNotEmpty($ipAddress2->getId());
        $ipAddress3 = new IpAddress('10.10.10.10');
        $ipAddressRepository->upsert($ipAddress3);
        $this->assertEquals($ipAddress1->getId(), $ipAddress3->getId());
    }
}
