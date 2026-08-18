<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\Provider;

use MailVotech\LeadBundle\Event\ListFieldChoicesEvent;
use MailVotech\LeadBundle\Exception\ChoicesNotFoundException;
use MailVotech\LeadBundle\LeadEvents;
use MailVotech\LeadBundle\Provider\FieldChoicesProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class FieldChoicesProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MockObject&EventDispatcherInterface
     */
    private MockObject $dispatcher;

    private FieldChoicesProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->provider   = new FieldChoicesProvider($this->dispatcher);
    }

    public function testGetChoicesForFieldThatDoesNotHaveAnyChoices(): void
    {
        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->callback($this->setSomeChoicesLikeASubscriber()),
                LeadEvents::COLLECT_FILTER_CHOICES_FOR_LIST_FIELD_TYPE
            );

        $this->expectException(ChoicesNotFoundException::class);
        $this->provider->getChoicesForField('text', 'email');
    }

    public function testGetChoicesForFieldThatHasTypeChoices(): void
    {
        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->callback($this->setSomeChoicesLikeASubscriber()),
                LeadEvents::COLLECT_FILTER_CHOICES_FOR_LIST_FIELD_TYPE
            );

        // Calling it twice to ensure the cache is working and the event is triggered only once.
        $this->provider->getChoicesForField('country', 'country_field_a');
        $choices = $this->provider->getChoicesForField('country', 'country_field_a');

        $this->assertSame(['Czech Republic' => 'Czech Republic'], $choices);
    }

    public function testGetChoicesForFieldThatHasAliasChoices(): void
    {
        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->callback($this->setSomeChoicesLikeASubscriber()),
                LeadEvents::COLLECT_FILTER_CHOICES_FOR_LIST_FIELD_TYPE
            );

        // Calling it twice to ensure the cache is working and the event is triggered only once.
        $this->provider->getChoicesForField('select', 'select_a');
        $choices = $this->provider->getChoicesForField('select', 'select_a');

        $this->assertSame(['Choice A' => 'choice_a'], $choices);
    }

    private function setSomeChoicesLikeASubscriber(): callable
    {
        return function (ListFieldChoicesEvent $event): true {
            $event->setChoicesForFieldAlias(
                'select_a',
                ['Choice A' => 'choice_a']
            );

            $event->setChoicesForFieldType(
                'country',
                ['Czech Republic' => 'Czech Republic']
            );

            return true;
        };
    }
}
