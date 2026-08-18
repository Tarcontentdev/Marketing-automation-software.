<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Tests\Unit\Event;

use MailVotech\IntegrationsBundle\Event\MailVotechSyncFieldsLoadEvent;
use PHPUnit\Framework\TestCase;

final class MailVotechSyncFieldsLoadEventTest extends TestCase
{
    public function testWorkflow(): void
    {
        $objectName = 'object';
        $fields     = [
            'fieldKey' => 'fieldName',
        ];

        $newFieldKey   = 'newFieldKey';
        $newFieldValue = 'newFieldValue';

        $event = new MailVotechSyncFieldsLoadEvent($objectName, $fields);
        $this->assertSame($objectName, $event->getObjectName());
        $this->assertSame($fields, $event->getFields());
        $event->addField($newFieldKey, $newFieldValue);
        $this->assertSame(
            array_merge($fields, [$newFieldKey => $newFieldValue]),
            $event->getFields()
        );
    }
}
