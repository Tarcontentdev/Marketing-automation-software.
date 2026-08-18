<?php

declare(strict_types=1);

namespace MailVotech\DynamicContentBundle\EventListener;

use MailVotech\CoreBundle\CoreEvents;
use MailVotech\CoreBundle\DTO\GlobalSearchFilterDTO;
use MailVotech\CoreBundle\Event\GlobalSearchEvent;
use MailVotech\CoreBundle\Service\GlobalSearch;
use MailVotech\DynamicContentBundle\Model\DynamicContentModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class SearchSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private DynamicContentModel $dynamicContentModel,
        private GlobalSearch $globalSearch,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CoreEvents::GLOBAL_SEARCH  => ['onGlobalSearch', 0],
        ];
    }

    public function onGlobalSearch(GlobalSearchEvent $event): void
    {
        $filterDTO = new GlobalSearchFilterDTO($event->getSearchString());
        $results   = $this->globalSearch->performSearch(
            $filterDTO,
            $this->dynamicContentModel,
            '@MailVotechDynamicContent/SubscribedEvents/Search/global.html.twig'
        );

        if ([] !== $results) {
            $event->addResults('mailvotech.dynamicContent.dynamicContent', $results);
        }
    }
}
