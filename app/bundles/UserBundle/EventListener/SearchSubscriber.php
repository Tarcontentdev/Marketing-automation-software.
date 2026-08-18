<?php

namespace MailVotech\UserBundle\EventListener;

use MailVotech\CoreBundle\CoreEvents;
use MailVotech\CoreBundle\DTO\GlobalSearchFilterDTO;
use MailVotech\CoreBundle\Event as MailVotechEvents;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\CoreBundle\Service\GlobalSearch;
use MailVotech\UserBundle\Model\RoleModel;
use MailVotech\UserBundle\Model\UserModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class SearchSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private UserModel $userModel,
        private RoleModel $userRoleModel,
        private CorePermissions $security,
        private GlobalSearch $globalSearch,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CoreEvents::GLOBAL_SEARCH => [
                ['onGlobalSearchUser', 0],
                ['onGlobalSearchRoles', 0],
            ],
            CoreEvents::BUILD_COMMAND_LIST => ['onBuildCommandList', 0],
        ];
    }

    public function onGlobalSearchUser(MailVotechEvents\GlobalSearchEvent $event): void
    {
        $filterDTO = new GlobalSearchFilterDTO($event->getSearchString());
        $results   = $this->globalSearch->performSearch(
            $filterDTO,
            $this->userModel,
            '@MailVotechUser/SubscribedEvents/Search/global_user.html.twig'
        );

        if ([] !== $results) {
            $event->addResults('mailvotech.user.users', $results);
        }
    }

    public function onGlobalSearchRoles(MailVotechEvents\GlobalSearchEvent $event): void
    {
        $filterDTO = new GlobalSearchFilterDTO($event->getSearchString());
        $results   = $this->globalSearch->performSearch(
            $filterDTO,
            $this->userRoleModel,
            '@MailVotechUser/SubscribedEvents/Search/global_role.html.twig'
        );

        if ([] !== $results) {
            $event->addResults('mailvotech.user.roles', $results);
        }
    }

    public function onBuildCommandList(MailVotechEvents\CommandListEvent $event): void
    {
        if ($this->security->isGranted('user:users:view')) {
            $event->addCommands(
                'mailvotech.user.users',
                $this->userModel->getCommandList()
            );
        }
        if ($this->security->isGranted('user:roles:view')) {
            $event->addCommands(
                'mailvotech.user.roles',
                $this->userRoleModel->getCommandList()
            );
        }
    }
}
