<?php

declare(strict_types=1);

namespace MailVotech\ApiBundle\EventListener;

use MailVotech\ApiBundle\Model\ClientModel;
use MailVotech\CoreBundle\CoreEvents;
use MailVotech\CoreBundle\DTO\GlobalSearchFilterDTO;
use MailVotech\CoreBundle\Event as MailVotechEvents;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\CoreBundle\Service\GlobalSearch;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class SearchSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ClientModel $apiClientModel,
        private CorePermissions $security,
        private GlobalSearch $globalSearch,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CoreEvents::GLOBAL_SEARCH      => ['onGlobalSearch', 0],
            CoreEvents::BUILD_COMMAND_LIST => ['onBuildCommandList', 0],
        ];
    }

    public function onGlobalSearch(MailVotechEvents\GlobalSearchEvent $event): void
    {
        $filterDTO = new GlobalSearchFilterDTO($event->getSearchString());
        $results   = $this->globalSearch->performSearch(
            $filterDTO,
            $this->apiClientModel,
            '@MailVotechApi/SubscribedEvents/Search/global.html.twig'
        );

        if ([] !== $results) {
            $event->addResults('mailvotech.api.client.menu.index', $results);
        }
    }

    public function onBuildCommandList(MailVotechEvents\CommandListEvent $event): void
    {
        if ($this->security->isGranted('api:clients:view')) {
            $event->addCommands(
                'mailvotech.api.client.header.index',
                $this->apiClientModel->getCommandList()
            );
        }
    }
}
