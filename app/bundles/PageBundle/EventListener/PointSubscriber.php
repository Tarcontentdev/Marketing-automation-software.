<?php

namespace MailVotech\PageBundle\EventListener;

use MailVotech\PageBundle\Event as Events;
use MailVotech\PageBundle\Form\Type\PointActionPageHitType;
use MailVotech\PageBundle\Form\Type\PointActionUrlHitType;
use MailVotech\PageBundle\Helper\PointActionHelper;
use MailVotech\PageBundle\PageEvents;
use MailVotech\PointBundle\Event\PointBuilderEvent;
use MailVotech\PointBundle\Model\PointModel;
use MailVotech\PointBundle\PointEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class PointSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private PointModel $pointModel,
        private PointActionHelper $pointActionHelper,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PointEvents::POINT_ON_BUILD => ['onPointBuild', 0],
            PageEvents::PAGE_ON_HIT     => ['onPageHit', 0],
        ];
    }

    public function onPointBuild(PointBuilderEvent $event): void
    {
        $action = [
            'group'       => 'mailvotech.page.point.action',
            'label'       => 'mailvotech.page.point.action.pagehit',
            'description' => 'mailvotech.page.point.action.pagehit_descr',
            'callback'    => [PointActionHelper::class, 'validatePageHit'],
            'formType'    => PointActionPageHitType::class,
        ];

        $event->addAction('page.hit', $action);

        $action = [
            'group'       => 'mailvotech.page.point.action',
            'label'       => 'mailvotech.page.point.action.urlhit',
            'description' => 'mailvotech.page.point.action.urlhit_descr',
            'callback'    => [$this->pointActionHelper, 'validateUrlHit'],
            'formType'    => PointActionUrlHitType::class,
            'formTheme'   => '@MailVotechPage/FormTheme/Point/pointaction_urlhit_widget.html.twig',
        ];

        $event->addAction('url.hit', $action);
    }

    /**
     * Trigger point actions for page hits.
     */
    public function onPageHit(Events\PageHitEvent $event): void
    {
        if ($event->getPage()) {
            // MailVotech Landing Page was hit
            $this->pointModel->triggerAction('page.hit', $event->getHit(), null, $event->getLead());
        } else {
            // MailVotech Tracking Pixel was hit
            $this->pointModel->triggerAction('url.hit', $event->getHit(), null, $event->getLead());
        }
    }
}
