<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Tests\Unit\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\MatcherInterface;
use Knp\Menu\MenuFactory;
use MailVotech\CoreBundle\CoreEvents;
use MailVotech\CoreBundle\Event\MenuEvent;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Menu\MenuBuilder;
use MailVotech\CoreBundle\Menu\MenuHelper;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\PluginBundle\Helper\IntegrationHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final class MenuBuilderTest extends TestCase
{
    private FactoryInterface $factory;

    private MatcherInterface&Stub $matcher;

    private EventDispatcherInterface&MockObject $dispatcher;

    private MenuHelper $menuHelper;

    protected function setUp(): void
    {
        $this->factory    = new MenuFactory();
        $this->matcher    = $this->createStub(MatcherInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->menuHelper = new MenuHelper(
            $this->createStub(CorePermissions::class),
            $this->createStub(RequestStack::class),
            $this->createStub(CoreParametersHelper::class),
            $this->createStub(IntegrationHelper::class)
        );
    }

    public function testMainMenuKeepsUriOnlyItems(): void
    {
        $menuName = uniqid('uriOnlyItems', false);

        $this->dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(function (MenuEvent $event, string $eventName): MenuEvent {
                $this->assertSame(CoreEvents::BUILD_MENU, $eventName);

                $event->addMenuItems([
                    'mailvotech.contribute.menu.index' => [
                        'uri'       => 'https://mailvotech.org',
                        'priority'  => 0,
                        'iconClass' => 'ri-hand-coin-fill',
                    ],
                ]);

                return $event;
            });

        $builder = new MenuBuilder($this->factory, $this->matcher, $this->dispatcher, $this->menuHelper);

        $menu = $builder->__call($menuName, []);

        $this->assertInstanceOf(ItemInterface::class, $menu);
        $menuItem = $menu->getChild('mailvotech.contribute.menu.index');

        $this->assertInstanceOf(ItemInterface::class, $menuItem);
        $this->assertSame('https://mailvotech.org', $menuItem->getUri());
    }
}
