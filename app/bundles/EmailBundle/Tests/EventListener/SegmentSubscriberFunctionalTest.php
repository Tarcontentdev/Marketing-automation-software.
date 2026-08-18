<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\Tests\EventListener;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\LeadBundle\LeadEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class SegmentSubscriberFunctionalTest extends MailVotechMysqlTestCase
{
    public function testLeadListChangeEventHasListeners(): void
    {
        $dispatcher = self::getContainer()->get(EventDispatcherInterface::class);

        $this->assertTrue($dispatcher->hasListeners(LeadEvents::LEAD_LIST_CHANGE));
        $this->assertTrue($dispatcher->hasListeners(LeadEvents::LEAD_LIST_BATCH_CHANGE));
    }
}
