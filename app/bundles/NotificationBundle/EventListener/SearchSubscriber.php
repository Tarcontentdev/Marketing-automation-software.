<?php

declare(strict_types=1);

namespace MailVotech\NotificationBundle\EventListener;

use MailVotech\CoreBundle\CoreEvents;
use MailVotech\CoreBundle\DTO\GlobalSearchFilterDTO;
use MailVotech\CoreBundle\Event as MailVotechEvents;
use MailVotech\CoreBundle\Service\GlobalSearch;
use MailVotech\NotificationBundle\Model\NotificationModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class SearchSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private NotificationModel $model,
        private GlobalSearch $globalSearch,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CoreEvents::GLOBAL_SEARCH => [
                ['onGlobalSearchWebNotification', 0],
                ['onGlobalSearchMobileNotification', 0],
            ],
        ];
    }

    public function onGlobalSearchWebNotification(MailVotechEvents\GlobalSearchEvent $event): void
    {
        $filterDTO = new GlobalSearchFilterDTO($event->getSearchString());
        $filterDTO->setFilters([
            'where'  => [
                [
                    'expr' => 'eq',
                    'col'  => 'mobile',
                    'val'  => 0,
                ],
            ],
        ]);
        $results = $this->globalSearch->performSearch(
            $filterDTO,
            $this->model,
            '@MailVotechNotification/SubscribedEvents/Search/global-web.html.twig'
        );

        if ([] !== $results) {
            $event->addResults('mailvotech.notification.notification.header', $results);
        }
    }

    public function onGlobalSearchMobileNotification(MailVotechEvents\GlobalSearchEvent $event): void
    {
        $filterDTO = new GlobalSearchFilterDTO($event->getSearchString());
        $filterDTO->setFilters([
            'where'  => [
                [
                    'expr' => 'eq',
                    'col'  => 'mobile',
                    'val'  => 1,
                ],
            ],
        ]);
        $results = $this->globalSearch->performSearch(
            $filterDTO,
            $this->model,
            '@MailVotechNotification/SubscribedEvents/Search/global-mobile.html.twig'
        );

        if ([] !== $results) {
            $event->addResults('mailvotech.notification.mobile_notification.header', $results);
        }
    }
}
