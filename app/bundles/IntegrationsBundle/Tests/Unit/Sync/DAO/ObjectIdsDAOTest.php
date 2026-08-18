<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Tests\Unit\Sync\DAO;

use MailVotech\IntegrationsBundle\Sync\DAO\Sync\ObjectIdsDAO;
use MailVotech\IntegrationsBundle\Sync\Exception\ObjectNotFoundException;
use PHPUnit\Framework\TestCase;

final class ObjectIdsDAOTest extends TestCase
{
    public function testWorkflow(): void
    {
        $objectIdsDAO = ObjectIdsDAO::createFromCliOptions(
            [
                'contact:123',
                'contact:124',
                'company:12',
                'company:13',
                'Lead:sowiern',
                'Lead:sowie4n',
            ]
        );

        $objectIdsDAO->addObjectId('company', '234');

        $this->assertSame(['123', '124'], $objectIdsDAO->getObjectIdsFor('contact'));
        $this->assertSame(['12', '13', '234'], $objectIdsDAO->getObjectIdsFor('company'));
        $this->assertSame(['sowiern', 'sowie4n'], $objectIdsDAO->getObjectIdsFor('Lead'));

        $this->expectException(ObjectNotFoundException::class);
        $objectIdsDAO->getObjectIdsFor('Unicorn');
    }
}
