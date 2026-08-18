<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Tests\Unit\Sync\DAO\Sync\Order;

use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Order\ObjectChangeDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Order\OrderDAO;
use PHPUnit\Framework\TestCase;

final class OrderDAOTest extends TestCase
{
    /**
     * Test that the retry object is removed from the synced objects and the success object is present.
     */
    public function testGetSuccessfullySyncedObjects(): void
    {
        $orderDAO      = new OrderDAO(new \DateTimeImmutable(), false, 'IntegrationA');
        $successObject = new ObjectChangeDAO('IntegrationA', 'Contact', 'integration-id-1', 'lead', 123);
        $retryObject   = new ObjectChangeDAO('IntegrationA', 'Contact', 'integration-id-2', 'lead', 456);

        $orderDAO->addObjectChange($successObject);
        $orderDAO->addObjectChange($retryObject);
        $orderDAO->retrySyncLater($retryObject);

        $this->assertSame([$successObject], $orderDAO->getSuccessfullySyncedObjects());
    }
}
