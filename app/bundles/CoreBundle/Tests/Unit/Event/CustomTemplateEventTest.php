<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Tests\Unit\Event;

use MailVotech\CoreBundle\Event\CustomTemplateEvent;

final class CustomTemplateEventTest extends \PHPUnit\Framework\TestCase
{
    public function testNullRequestDoesNotThrowException(): void
    {
        $event = new CustomTemplateEvent(null, 'test');
        $this->assertSame('test', $event->getTemplate());
    }

    public function testEmptyTemplateThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new CustomTemplateEvent();
    }
}
