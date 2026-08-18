<?php

declare(strict_types=1);

namespace MailVotech\MessengerBundle\Tests\MessageHandler;

use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Entity\LeadRepository;
use MailVotech\MessengerBundle\Message\PageHitNotification;
use MailVotech\MessengerBundle\MessageHandler\PageHitNotificationHandler;
use MailVotech\PageBundle\Entity\Hit;
use MailVotech\PageBundle\Entity\HitRepository;
use MailVotech\PageBundle\Entity\Page;
use MailVotech\PageBundle\Entity\PageRepository;
use MailVotech\PageBundle\Entity\Redirect;
use MailVotech\PageBundle\Entity\RedirectRepository;
use MailVotech\PageBundle\Model\PageModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

final class PageHitNotificationHandlerTest extends TestCase
{
    public function testInvoke(): void
    {
        [$hitId, $pageId, $leadId, $redirectId]                 = [random_int(1, 1000), random_int(1, 1000), random_int(1, 1000), random_int(1, 1000)];

        $redirectObject = new Redirect();
        $redirectObject->setRedirectId((string) $redirectId);

        [$hitObject, $pageObject, $leadObject] = [
            (new Hit())->setCode(7),
            (new Page())->setAlias('james_bond'),
            (new Lead())->setId($leadId),
        ];

        $hitRepoMock = $this->createMock(HitRepository::class);
        $hitRepoMock
            ->expects($this->once())
            ->method('find')
            ->with($hitId)
            ->willReturn($hitObject);

        $pageRepoMock = $this->createMock(PageRepository::class);
        $pageRepoMock->expects($this->once())
            ->method('find')
            ->with($pageId)
            ->willReturn($pageObject);

        $redirectRepoMock = $this->createMock(RedirectRepository::class);
        $redirectRepoMock
            ->expects($this->never())
            ->method('find')
            ->with($redirectId)
            ->willReturn($redirectObject);

        $leadRepoMock = $this->createMock(LeadRepository::class);
        $leadRepoMock
            ->expects($this->once())
            ->method('find')
            ->with($leadId)
            ->willReturn($leadObject);

        $request = new Request();
        $request->query->set('testMe', 'I am here');

        /** @var MockObject|PageModel $pageModelMock */
        $pageModelMock = $this->createMock(PageModel::class);
        $pageModelMock
            ->expects($this->exactly(1))
            ->method('processPageHit')
            ->with($hitObject, $pageObject, $request, $leadObject, false, false);

        $message = new PageHitNotification($hitId, $request, false, false, $pageId, $leadId);

        /** @var MockObject|LoggerInterface $loggerMock */
        $loggerMock = $this->createStub(LoggerInterface::class);

        $handler = new PageHitNotificationHandler(
            $pageRepoMock, $hitRepoMock, $leadRepoMock, $loggerMock, $redirectRepoMock, $pageModelMock
        );

        $handler->__invoke($message);
    }
}
