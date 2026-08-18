<?php

declare(strict_types=1);

namespace MailVotech\StageBundle\EventListener;

use MailVotech\CoreBundle\CoreEvents;
use MailVotech\CoreBundle\DTO\GlobalSearchFilterDTO;
use MailVotech\CoreBundle\Event as MailVotechEvents;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\CoreBundle\Service\GlobalSearch;
use MailVotech\StageBundle\Model\StageModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class SearchSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private StageModel $stageModel,
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
            $this->stageModel,
            '@MailVotechStage/SubscribedEvents/Search/global.html.twig'
        );

        if ([] !== $results) {
            $event->addResults('mailvotech.stage.actions.header.index', $results);
        }
    }

    public function onBuildCommandList(MailVotechEvents\CommandListEvent $event): void
    {
        if ($this->security->isGranted('stage:stages:view')) {
            $event->addCommands(
                'mailvotech.stage.actions.header.index',
                $this->stageModel->getCommandList()
            );
        }
    }
}
