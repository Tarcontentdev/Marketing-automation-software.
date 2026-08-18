<?php

declare(strict_types=1);

namespace MailVotech\AssetBundle\EventListener;

use MailVotech\AssetBundle\AssetEvents;
use MailVotech\PageBundle\Event\PageBuilderEvent;
use MailVotech\PageBundle\PageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class PageSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            PageEvents::PAGE_ON_BUILD => ['OnPageBuild', 0],
        ];
    }

    /**
     * Add forms to available page tokens.
     */
    public function onPageBuild(PageBuilderEvent $event): void
    {
        if ($event->abTestWinnerCriteriaRequested()) {
            // add AB Test Winner Criteria
            $assetDownloads = [
                'group'    => 'mailvotech.asset.abtest.criteria',
                'label'    => 'mailvotech.asset.abtest.criteria.downloads',
                'event'    => AssetEvents::ON_DETERMINE_DOWNLOAD_RATE_WINNER,
            ];
            $event->addAbTestWinnerCriteria('asset.downloads', $assetDownloads);
        }
    }
}
