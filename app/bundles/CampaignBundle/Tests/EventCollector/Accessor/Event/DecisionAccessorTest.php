<?php

declare(strict_types=1);

namespace MailVotech\CampaignBundle\Tests\EventCollector\Accessor\Event;

use MailVotech\CampaignBundle\EventCollector\Accessor\Event\DecisionAccessor;

final class DecisionAccessorTest extends \PHPUnit\Framework\TestCase
{
    public function testEventNameIsReturned(): void
    {
        $accessor = new DecisionAccessor(['eventName' => 'test']);

        $this->assertEquals('test', $accessor->getEventName());
    }

    public function testExtraParamIsReturned(): void
    {
        $accessor = new DecisionAccessor(['eventName' => 'test', 'foo' => 'bar']);

        $this->assertEquals(['foo' => 'bar'], $accessor->getExtraProperties());
    }
}
