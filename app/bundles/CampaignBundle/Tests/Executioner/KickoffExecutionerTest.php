<?php

declare(strict_types=1);

namespace MailVotech\CampaignBundle\Tests\Executioner;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use MailVotech\CampaignBundle\Entity\Campaign;
use MailVotech\CampaignBundle\Entity\Event;
use MailVotech\CampaignBundle\Executioner\ContactFinder\KickoffContactFinder;
use MailVotech\CampaignBundle\Executioner\ContactFinder\Limiter\ContactLimiter;
use MailVotech\CampaignBundle\Executioner\EventExecutioner;
use MailVotech\CampaignBundle\Executioner\Helper\EventRedirectionHelper;
use MailVotech\CampaignBundle\Executioner\KickoffExecutioner;
use MailVotech\CampaignBundle\Executioner\Result\Counter;
use MailVotech\CampaignBundle\Executioner\Scheduler\EventScheduler;
use MailVotech\CampaignBundle\Executioner\Scheduler\Exception\NotSchedulableException;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\ProcessSignal\ProcessSignalService;
use MailVotech\CoreBundle\Translation\Translator;
use MailVotech\LeadBundle\Entity\Lead;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class KickoffExecutionerTest extends \PHPUnit\Framework\TestCase
{
    private MockObject&KickoffContactFinder $kickoffContactFinder;

    private \PHPUnit\Framework\MockObject\Stub&Translator $translator;

    private MockObject&EventExecutioner $executioner;

    private MockObject&EventScheduler $scheduler;

    private \PHPUnit\Framework\MockObject\Stub&CoreParametersHelper $coreParametersHelper;

    private \PHPUnit\Framework\MockObject\Stub&EventRedirectionHelper $redirectionHelper;

    private \PHPUnit\Framework\MockObject\Stub&EntityManagerInterface $entityManager;

    private \PHPUnit\Framework\MockObject\Stub&EventDispatcherInterface $eventDispatcher;

    protected function setUp(): void
    {
        $this->kickoffContactFinder = $this->createMock(KickoffContactFinder::class);
        $this->translator           = $this->createStub(Translator::class);
        $this->executioner          = $this->createMock(EventExecutioner::class);
        $this->scheduler            = $this->createMock(EventScheduler::class);
        $this->coreParametersHelper = $this->createStub(CoreParametersHelper::class);
        $this->redirectionHelper    = $this->createStub(EventRedirectionHelper::class);
        $this->entityManager        = $this->createStub(EntityManagerInterface::class);
        $this->eventDispatcher      = $this->createStub(EventDispatcherInterface::class);
    }

    public function testNoContactsResultInEmptyResults(): void
    {
        $campaign = $this->createMock(Campaign::class);
        $campaign->expects($this->once())
            ->method('getRootEvents')
            ->willReturn(new ArrayCollection());

        $limiter = new ContactLimiter(0, 0, 0, 0);

        $counter = $this->getExecutioner()->execute($campaign, $limiter, new BufferedOutput());
        $this->assertInstanceOf(Counter::class, $counter);

        $this->assertEquals(0, $counter->getTotalEvaluated());
    }

    public function testEventsAreScheduledAndExecuted(): void
    {
        $this->kickoffContactFinder->expects($this->once())
            ->method('getContactCount')
            ->willReturn(2);

        $this->kickoffContactFinder->expects($this->exactly(3))
            ->method('getContacts')
            ->willReturnOnConsecutiveCalls(
                new ArrayCollection([3 => new Lead()]),
                new ArrayCollection([10 => new Lead()]),
                new ArrayCollection([])
            );

        $event    = new Event();
        $event2   = new Event();
        $campaign = new class() extends Campaign {
            /**
             * @var ArrayCollection<int,Event>
             */
            public ArrayCollection $rootEvents;

            /**
             * @return ArrayCollection<int,Event>
             */
            public function getRootEvents(): ArrayCollection
            {
                return $this->rootEvents;
            }
        };
        $campaign->rootEvents = new ArrayCollection([$event, $event2]);
        $event->setCampaign($campaign);
        $event2->setCampaign($campaign);

        $limiter = new ContactLimiter(0, 0, 0, 0);

        $this->scheduler->expects($this->exactly(4))
            ->method('getExecutionDateTime')
            ->willReturn(new \DateTime());

        $callbackCounter = 0;
        $this->scheduler->expects($this->exactly(4))
            ->method('validateAndScheduleEventForContacts')
            ->willReturnCallback(function () use (&$callbackCounter): void {
                ++$callbackCounter;
                if (in_array($callbackCounter, [3, 4])) {
                    throw new NotSchedulableException();
                }
            });

        $this->executioner->expects($this->exactly(1))
            ->method('executeEventsForContacts')->willReturnCallback(function (...$parameters): void {
                $this->assertCount(2, $parameters[0]);
                $this->assertInstanceOf(ArrayCollection::class, $parameters[1]);
                $this->assertInstanceOf(Counter::class, $parameters[2]);
            });

        $counter = $this->getExecutioner()->execute($campaign, $limiter, new BufferedOutput());
        $this->assertInstanceOf(Counter::class, $counter);

        $this->assertEquals(4, $counter->getTotalEvaluated());
        $this->assertEquals(2, $counter->getTotalScheduled());
    }

    private function getExecutioner(): KickoffExecutioner
    {
        return new KickoffExecutioner(
            new NullLogger(),
            $this->kickoffContactFinder,
            $this->translator,
            $this->executioner,
            $this->scheduler,
            $this->createStub(ProcessSignalService::class),
            $this->coreParametersHelper,
            $this->eventDispatcher,
            $this->redirectionHelper,
            $this->entityManager,
        );
    }
}
