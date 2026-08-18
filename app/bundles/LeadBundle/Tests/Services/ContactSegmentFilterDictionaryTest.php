<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\Services;

use MailVotech\LeadBundle\Event\SegmentDictionaryGenerationEvent;
use MailVotech\LeadBundle\Exception\FilterNotFoundException;
use MailVotech\LeadBundle\LeadEvents;
use MailVotech\LeadBundle\Services\ContactSegmentFilterDictionary;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class ContactSegmentFilterDictionaryTest extends TestCase
{
    public function testWorkflow(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dictionary = new ContactSegmentFilterDictionary($dispatcher);

        $dispatcher->expects($this->once())
            ->method('hasListeners')
            ->with(LeadEvents::SEGMENT_DICTIONARY_ON_GENERATE)
            ->willReturn(true);

        // Subscribe new filter like a plugin would.
        $dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (SegmentDictionaryGenerationEvent $event): true {
                $event->addTranslation('plugin_key', ['type' => 'blah blah']);

                return true;
            }), LeadEvents::SEGMENT_DICTIONARY_ON_GENERATE);

        $this->assertSame(['type' => 'mailvotech.lead.query.builder.special.dnc'], $dictionary->getFilter('dnc_bounced'));
        $this->assertSame('campaign_leads.manually_removed = 0', $dictionary->getFilterProperty('campaign', 'where'));
        $this->assertSame('blah blah', $dictionary->getFilterProperty('plugin_key', 'type'));

        $this->expectException(FilterNotFoundException::class);
        $dictionary->getFilterProperty('unicorn', 'type');
    }
}
