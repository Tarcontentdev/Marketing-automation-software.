<?php

declare(strict_types=1);

namespace MailVotech\CampaignBundle\Tests\Executioner\Dispatcher;

use Doctrine\Common\Collections\ArrayCollection;
use MailVotech\CampaignBundle\CampaignEvents;
use MailVotech\CampaignBundle\Entity\LeadEventLog;
use MailVotech\CampaignBundle\Event\DecisionEvent;
use MailVotech\CampaignBundle\Event\DecisionResultsEvent;
use MailVotech\CampaignBundle\EventCollector\Accessor\Event\DecisionAccessor;
use MailVotech\CampaignBundle\Executioner\Dispatcher\DecisionDispatcher;
use MailVotech\CampaignBundle\Executioner\Dispatcher\LegacyEventDispatcher;
use MailVotech\CampaignBundle\Executioner\Result\EvaluatedContacts;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class DecisionDispatcherTest extends TestCase
{
    private MockObject&EventDispatcherInterface $dispatcher;

    private MockObject&LegacyEventDispatcher $legacyDispatcher;

    private MockObject&DecisionAccessor $config;

    private DecisionDispatcher $decisionDispatcher;

    protected function setUp(): void
    {
        $this->dispatcher         = $this->createMock(EventDispatcherInterface::class);
        /** @phpstan-ignore classConstant.deprecatedClass */
        $this->legacyDispatcher   = $this->createMock(LegacyEventDispatcher::class);
        $this->config             = $this->createMock(DecisionAccessor::class);
        $this->decisionDispatcher = new DecisionDispatcher($this->dispatcher, $this->legacyDispatcher);
    }

    public function testDecisionEventIsDispatched(): void
    {
        $this->config->expects($this->once())
            ->method('getEventName')
            ->willReturn('something');

        $this->legacyDispatcher->expects($this->never())
            ->method('dispatchDecisionEvent');

        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(DecisionEvent::class), 'something');

        $this->decisionDispatcher->dispatchRealTimeEvent($this->config, new LeadEventLog(), null);
    }

    public function testDecisionEvaluationEventIsDispatched(): void
    {
        $this->config->expects($this->never())
            ->method('getEventName');

        $this->legacyDispatcher->expects($this->once())
            ->method('dispatchDecisionEvent');

        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(DecisionEvent::class), CampaignEvents::ON_EVENT_DECISION_EVALUATION);

        $this->decisionDispatcher->dispatchEvaluationEvent($this->config, new LeadEventLog());
    }

    public function testDecisionResultsEventIsDispatched(): void
    {
        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(DecisionResultsEvent::class), CampaignEvents::ON_EVENT_DECISION_EVALUATION_RESULTS);

        $this->decisionDispatcher->dispatchDecisionResultsEvent($this->config, new ArrayCollection([new LeadEventLog()]), new EvaluatedContacts());
    }
}
