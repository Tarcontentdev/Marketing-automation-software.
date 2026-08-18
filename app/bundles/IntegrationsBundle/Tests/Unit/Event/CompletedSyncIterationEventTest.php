<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Tests\Unit\Event;

use MailVotech\IntegrationsBundle\Event\CompletedSyncIterationEvent;
use MailVotech\IntegrationsBundle\Sync\DAO\Mapping\MappingManualDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\InputOptionsDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Order\OrderResultsDAO;
use PHPUnit\Framework\TestCase;

final class CompletedSyncIterationEventTest extends TestCase
{
    public function testGetters(): void
    {
        $mappingManual = new MappingManualDAO('foobar');
        $orderResults  = new OrderResultsDAO([], [], [], []);
        $iteration     = 1;
        $inputOptions  = new InputOptionsDAO(['integration' => 'foobar']);

        $event = new CompletedSyncIterationEvent($orderResults, $iteration, $inputOptions, $mappingManual);

        $this->assertSame($mappingManual->getIntegration(), $event->getIntegration());
        $this->assertSame($orderResults, $event->getOrderResults());
        $this->assertSame($iteration, $event->getIteration());
        $this->assertSame($inputOptions, $event->getInputOptions());
        $this->assertSame($mappingManual, $event->getMappingManual());
    }
}
