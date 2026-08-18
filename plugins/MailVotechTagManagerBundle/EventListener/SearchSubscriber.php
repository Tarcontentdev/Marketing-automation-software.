<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechTagManagerBundle\EventListener;

use MailVotech\CoreBundle\CoreEvents;
use MailVotech\CoreBundle\DTO\GlobalSearchFilterDTO;
use MailVotech\CoreBundle\Event\GlobalSearchEvent;
use MailVotech\CoreBundle\Service\GlobalSearch;
use MailVotechPlugin\MailVotechTagManagerBundle\Model\TagModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class SearchSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private TagModel $model,
        private GlobalSearch $globalSearch,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CoreEvents::GLOBAL_SEARCH => ['onGlobalSearch', 0],
        ];
    }

    public function onGlobalSearch(GlobalSearchEvent $event): void
    {
        $filterDTO = new GlobalSearchFilterDTO($event->getSearchString());
        $results   = $this->globalSearch->performSearch(
            $filterDTO,
            $this->model,
            '@MailVotechTagManager/SubscribedEvents/Search/global.html.twig'
        );

        if ([] !== $results) {
            $event->addResults('mailvotech.tagmanager.tag.header.index', $results);
        }
    }
}
