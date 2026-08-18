<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Tests\Command;

use MailVotech\CoreBundle\Entity\IpAddress;
use MailVotech\CoreBundle\Entity\IpAddressRepository;
use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;

final class UnusedIpDeleteCommandFunctionalTest extends MailVotechMysqlTestCase
{
    /**
     * @throws \Exception
     */
    public function testUnusedIpDeleteCommand(): void
    {
        // Emulate unused IP address.
        /** @var IpAddressRepository $ipAddressRepo */
        $ipAddressRepo = $this->em->getRepository(IpAddress::class);
        $ipAddressRepo->saveEntity(new IpAddress('127.0.0.1'));
        $count = $ipAddressRepo->count(['ipAddress' => '127.0.0.1']);
        $this->assertSame(1, $count);

        // Delete unused IP address.
        $this->testSymfonyCommand('mailvotech:unusedip:delete');

        $count = $ipAddressRepo->count(['ipAddress' => '127.0.0.1']);
        $this->assertSame(0, $count);
    }
}
