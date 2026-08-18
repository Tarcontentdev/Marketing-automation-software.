<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\Event;

use MailVotech\LeadBundle\Entity\Tag;
use MailVotech\LeadBundle\Event\TagMergeEvent;
use PHPUnit\Framework\TestCase;

final class TagMergeEventTest extends TestCase
{
    public function testConstructGettersSetters(): void
    {
        $primaryTag   = new Tag();
        $secondaryTag = new Tag();
        $event        = new TagMergeEvent($primaryTag, $secondaryTag);

        $this->assertSame($primaryTag, $event->getPrimaryTag());
        $this->assertSame($secondaryTag, $event->getSecondaryTag());
    }
}
