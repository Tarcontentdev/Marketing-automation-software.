<?php

declare(strict_types=1);

namespace MailVotech\PointBundle\EventListener;

use MailVotech\CoreBundle\CoreEvents;
use MailVotech\CoreBundle\DTO\GlobalSearchFilterDTO;
use MailVotech\CoreBundle\Event as MailVotechEvents;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\CoreBundle\Service\GlobalSearch;
use MailVotech\PointBundle\Model\PointGroupModel;
use MailVotech\PointBundle\Model\PointModel;
use MailVotech\PointBundle\Model\TriggerModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class SearchSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private PointModel $pointModel,
        private TriggerModel $pointTriggerModel,
        private PointGroupModel $pointGroupModel,
        private CorePermissions $security,
        private GlobalSearch $globalSearch,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CoreEvents::GLOBAL_SEARCH => [
                ['onGlobalSearchPointActions', 0],
                ['onGlobalSearchPointTriggers', 0],
                ['onGlobalSearchPointGroup', 0],
            ],
            CoreEvents::BUILD_COMMAND_LIST => ['onBuildCommandList', 0],
        ];
    }

    public function onGlobalSearchPointActions(MailVotechEvents\GlobalSearchEvent $event): void
    {
        $filterDTO = new GlobalSearchFilterDTO($event->getSearchString());
        $results   = $this->globalSearch->performSearch(
            $filterDTO,
            $this->pointModel,
            '@MailVotechPoint/SubscribedEvents/Search/global_point.html.twig'
        );

        if ([] !== $results) {
            $event->addResults('mailvotech.point.actions.header.index', $results);
        }
    }

    public function onGlobalSearchPointGroup(MailVotechEvents\GlobalSearchEvent $event): void
    {
        $filterDTO = new GlobalSearchFilterDTO($event->getSearchString());
        $results   = $this->globalSearch->performSearch(
            $filterDTO,
            $this->pointGroupModel,
            '@MailVotechPoint/SubscribedEvents/Search/global_group.html.twig'
        );

        if ([] !== $results) {
            $event->addResults('mailvotech.point.group.header.index', $results);
        }
    }

    public function onGlobalSearchPointTriggers(MailVotechEvents\GlobalSearchEvent $event): void
    {
        $filterDTO = new GlobalSearchFilterDTO($event->getSearchString());
        $results   = $this->globalSearch->performSearch(
            $filterDTO,
            $this->pointTriggerModel,
            '@MailVotechPoint/SubscribedEvents/Search/global_trigger.html.twig'
        );

        if ([] !== $results) {
            $event->addResults('mailvotech.point.trigger.header.index', $results);
        }
    }

    public function onBuildCommandList(MailVotechEvents\CommandListEvent $event): void
    {
        if ($this->security->isGranted('point:points:view')) {
            $event->addCommands(
                'mailvotech.point.actions.header.index',
                $this->pointModel->getCommandList()
            );
        }
    }
}
