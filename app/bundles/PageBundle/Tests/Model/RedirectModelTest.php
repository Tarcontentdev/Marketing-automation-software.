<?php

declare(strict_types=1);

namespace MailVotech\PageBundle\Tests\Model;

use Doctrine\ORM\EntityManagerInterface;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Helper\UserHelper;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\CoreBundle\Shortener\Shortener;
use MailVotech\CoreBundle\Translation\Translator;
use MailVotech\PageBundle\Entity\Redirect;
use MailVotech\PageBundle\Entity\RedirectRepository;
use MailVotech\PageBundle\Event\RedirectGenerationEvent;
use MailVotech\PageBundle\Model\RedirectModel;
use MailVotech\PageBundle\PageEvents;
use MailVotech\PageBundle\Tests\PageTestAbstract;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class RedirectModelTest extends PageTestAbstract
{
    public function testCreateRedirectEntityWhenCalledReturnsRedirect(): void
    {
        $redirectModel = $this->getRedirectModel();
        $entity        = $redirectModel->createRedirectEntity('http://some-url.com');

        $this->assertInstanceOf(Redirect::class, $entity);
    }

    public function testGenerateRedirectUrlWhenCalledReturnsValidUrl(): void
    {
        $redirect = new Redirect();
        $redirect->setUrl('http://some-url.com');
        $redirect->setRedirectId('redirect-id');

        $redirectModel = $this->getRedirectModel();
        $url           = $redirectModel->generateRedirectUrl($redirect);

        $this->assertStringContainsString('http://some-url.com', (string) $url);
    }

    public function testRedirectGenerationEvent(): void
    {
        $shortener = $this->createStub(Shortener::class);

        $dispatcher = new EventDispatcher();

        $url          = 'https://mailvotech.org';
        $clickthrough = ['foo' => 'bar'];

        $router = $this->createMock(Router::class);
        $router->expects($this->exactly(2))
            ->method('generate')
            ->willReturn($url);

        $model = new RedirectModel(
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(CorePermissions::class),
            $dispatcher,
            $router,
            $this->createStub(Translator::class),
            $this->createStub(UserHelper::class),
            $this->createStub(LoggerInterface::class),
            $this->createStub(CoreParametersHelper::class),
        );
        $model->autowireRedirectModel(
            $shortener,
            $this->createStub(RedirectRepository::class)
        );

        $redirect = new Redirect();
        $redirect->setUrl($url);

        // URL should just have foo = bar in the CT
        $url = $model->generateRedirectUrl($redirect, $clickthrough);
        $this->assertEquals('https://mailvotech.org?ct=YToxOntzOjM6ImZvbyI7czozOiJiYXIiO30%3D', $url);

        // Add the listener to append something else to the CT
        $dispatcher->addListener(
            PageEvents::ON_REDIRECT_GENERATE,
            function (RedirectGenerationEvent $event): void {
                $event->setInClickthrough('bar', 'foo');
            }
        );
        $url = $model->generateRedirectUrl($redirect, $clickthrough);
        $this->assertEquals('https://mailvotech.org?ct=YToyOntzOjM6ImZvbyI7czozOiJiYXIiO3M6MzoiYmFyIjtzOjM6ImZvbyI7fQ%3D%3D', $url);
    }
}
