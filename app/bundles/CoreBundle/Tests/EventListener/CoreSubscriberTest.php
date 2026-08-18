<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Tests\EventListener;

use MailVotech\CoreBundle\CoreEvents;
use MailVotech\CoreBundle\EventListener\CoreSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Http\SecurityEvents;

final class CoreSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        $this->assertSame([
            CoreEvents::BUILD_MENU            => ['onBuildMenu', 9999],
            CoreEvents::BUILD_ROUTE           => ['onBuildRoute', 0],
            CoreEvents::FETCH_ICONS           => ['onFetchIcons', 9999],
            SecurityEvents::INTERACTIVE_LOGIN => ['onSecurityInteractiveLogin', 0],
        ], CoreSubscriber::getSubscribedEvents());
    }
}
