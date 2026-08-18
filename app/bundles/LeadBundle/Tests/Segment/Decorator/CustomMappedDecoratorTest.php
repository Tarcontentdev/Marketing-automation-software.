<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\Segment\Decorator;

use MailVotech\LeadBundle\Segment\ContactSegmentFilterCrate;
use MailVotech\LeadBundle\Segment\ContactSegmentFilterOperator;
use MailVotech\LeadBundle\Segment\Decorator\CustomMappedDecorator;
use MailVotech\LeadBundle\Services\ContactSegmentFilterDictionary;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

#[CoversClass(CustomMappedDecorator::class)]
final class CustomMappedDecoratorTest extends \PHPUnit\Framework\TestCase
{
    public function testGetField(): void
    {
        $customMappedDecorator = $this->getDecorator();

        $contactSegmentFilterCrate = new ContactSegmentFilterCrate([
            'field'    => 'lead_email_read_count',
        ]);

        $this->assertSame('open_count', $customMappedDecorator->getField($contactSegmentFilterCrate));
    }

    public function testGetTable(): void
    {
        $customMappedDecorator = $this->getDecorator();

        $contactSegmentFilterCrate = new ContactSegmentFilterCrate([
            'field'    => 'lead_email_read_count',
        ]);

        $this->assertSame(MAILVOTECH_TABLE_PREFIX.'email_stats', $customMappedDecorator->getTable($contactSegmentFilterCrate));
    }

    public function testGetQueryType(): void
    {
        $customMappedDecorator = $this->getDecorator();

        $contactSegmentFilterCrate = new ContactSegmentFilterCrate([
            'field'    => 'dnc_bounced',
        ]);

        $this->assertSame('mailvotech.lead.query.builder.special.dnc', $customMappedDecorator->getQueryType($contactSegmentFilterCrate));
    }

    public function testGetForeignContactColumn(): void
    {
        $customMappedDecorator = $this->getDecorator();

        $contactSegmentFilterCrate = new ContactSegmentFilterCrate([
            'field'    => 'lead_email_read_count',
        ]);

        $this->assertSame('lead_id', $customMappedDecorator->getForeignContactColumn($contactSegmentFilterCrate));
    }

    private function getDecorator(): CustomMappedDecorator
    {
        $contactSegmentFilterDictionary = new ContactSegmentFilterDictionary($this->createStub(EventDispatcherInterface::class));

        return new CustomMappedDecorator($this->createStub(ContactSegmentFilterOperator::class), $contactSegmentFilterDictionary);
    }
}
