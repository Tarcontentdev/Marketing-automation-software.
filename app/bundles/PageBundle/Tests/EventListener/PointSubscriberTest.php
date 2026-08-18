<?php

declare(strict_types=1);

namespace MailVotech\PageBundle\Tests\EventListener;

use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\PageBundle\Entity\Hit;
use MailVotech\PageBundle\Entity\Page;
use MailVotech\PageBundle\Event\PageHitEvent;
use MailVotech\PageBundle\EventListener\PointSubscriber;
use MailVotech\PageBundle\Form\Type\PointActionPageHitType;
use MailVotech\PageBundle\Form\Type\PointActionUrlHitType;
use MailVotech\PageBundle\Helper\PointActionHelper;
use MailVotech\PointBundle\Event\PointBuilderEvent;
use MailVotech\PointBundle\Model\PointModel;
use PHPUnit\Framework\TestCase;

final class PointSubscriberTest extends TestCase
{
    public function testSubscribedEvents(): void
    {
        $this->assertSame([
            'mailvotech.point_on_build' => ['onPointBuild', 0],
            'mailvotech.page_on_hit'    => ['onPageHit', 0],
        ], PointSubscriber::getSubscribedEvents());
    }

    public function testPointBuildAddsActions(): void
    {
        $pointModel        = $this->createStub(PointModel::class);
        $pointBuilderEvent = $this->createMock(PointBuilderEvent::class);
        $pointActionHelper = $this->createStub(PointActionHelper::class);
        $matcher           = $this->exactly(2);

        $pointBuilderEvent->expects($matcher)->method('addAction')->willReturnCallback(function (...$parameters) use ($matcher, $pointActionHelper): void {
            if (1 === $matcher->numberOfInvocations()) {
                $this->assertSame('page.hit', $parameters[0]);
                $this->assertSame([
                    'group'       => 'mailvotech.page.point.action',
                    'label'       => 'mailvotech.page.point.action.pagehit',
                    'description' => 'mailvotech.page.point.action.pagehit_descr',
                    'callback'    => [PointActionHelper::class, 'validatePageHit'],
                    'formType'    => PointActionPageHitType::class,
                ], $parameters[1]);
            }
            if (2 === $matcher->numberOfInvocations()) {
                $this->assertSame('url.hit', $parameters[0]);
                $this->assertSame([
                    'group'       => 'mailvotech.page.point.action',
                    'label'       => 'mailvotech.page.point.action.urlhit',
                    'description' => 'mailvotech.page.point.action.urlhit_descr',
                    'callback'    => [$pointActionHelper, 'validateUrlHit'],
                    'formType'    => PointActionUrlHitType::class,
                    'formTheme'   => '@MailVotechPage/FormTheme/Point/pointaction_urlhit_widget.html.twig',
                ], $parameters[1]);
            }
        });

        $pointSubscriber = new PointSubscriber($pointModel, $pointActionHelper);
        $pointSubscriber->onPointBuild($pointBuilderEvent);
    }

    public function testPageHitTriggersPageHitWhenPageIsSet(): void
    {
        $pageHitEvent      = $this->createMock(PageHitEvent::class);
        $page              = $this->createStub(Page::class);
        $hit               = $this->createStub(Hit::class);
        $lead              = $this->createStub(Lead::class);
        $pointModel        = $this->createMock(PointModel::class);
        $pointActionHelper = $this->createStub(PointActionHelper::class);

        $pageHitEvent->expects($this->once())->method('getPage')->willReturn($page);
        $pageHitEvent->expects($this->once())->method('getHit')->willReturn($hit);
        $pageHitEvent->expects($this->once())->method('getLead')->willReturn($lead);
        $pointModel->expects($this->once())->method('triggerAction')->with('page.hit', $hit, null, $lead);

        $pointSubscriber = new PointSubscriber($pointModel, $pointActionHelper);
        $pointSubscriber->onPageHit($pageHitEvent);
    }

    public function testURLHitTriggersPageHitWhenPageIsSet(): void
    {
        $pageHitEvent      = $this->createMock(PageHitEvent::class);
        $hit               = $this->createStub(Hit::class);
        $lead              = $this->createStub(Lead::class);
        $pointModel        = $this->createMock(PointModel::class);
        $pointActionHelper = $this->createStub(PointActionHelper::class);

        $pageHitEvent->expects($this->once())->method('getPage')->willReturn(null);
        $pageHitEvent->expects($this->once())->method('getHit')->willReturn($hit);
        $pageHitEvent->expects($this->once())->method('getLead')->willReturn($lead);
        $pointModel->expects($this->once())->method('triggerAction')->with('url.hit', $hit, null, $lead);

        $pointSubscriber = new PointSubscriber($pointModel, $pointActionHelper);
        $pointSubscriber->onPageHit($pageHitEvent);
    }
}
