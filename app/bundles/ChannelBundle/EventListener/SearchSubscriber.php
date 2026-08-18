<?php

declare(strict_types=1);

namespace MailVotech\ChannelBundle\EventListener;

use MailVotech\ChannelBundle\Model\MessageModel;
use MailVotech\CoreBundle\CoreEvents;
use MailVotech\CoreBundle\DTO\GlobalSearchFilterDTO;
use MailVotech\CoreBundle\Event\GlobalSearchEvent;
use MailVotech\CoreBundle\Service\GlobalSearch;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class SearchSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MessageModel $model,
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
        $results = $this->globalSearch->performSearch(
            new GlobalSearchFilterDTO($event->getSearchString()),
            $this->model,
            '@MailVotechChannel/SubscribedEvents/Search/global.html.twig'
        );

        if ([] !== $results) {
            $event->addResults('mailvotech.messages.header', $results);
        }
    }
}
