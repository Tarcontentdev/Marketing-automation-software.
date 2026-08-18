<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\EventListener;

use MailVotech\CoreBundle\CoreEvents;
use MailVotech\CoreBundle\DTO\GlobalSearchFilterDTO;
use MailVotech\CoreBundle\Event as MailVotechEvents;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\CoreBundle\Service\GlobalSearch;
use MailVotech\EmailBundle\Model\EmailModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class SearchSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EmailModel $emailModel,
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
            $this->emailModel,
            '@MailVotechEmail/SubscribedEvents/Search/global.html.twig'
        );

        if ([] !== $results) {
            $event->addResults('mailvotech.email.emails', $results);
        }
    }

    public function onBuildCommandList(MailVotechEvents\CommandListEvent $event): void
    {
        if ($this->security->isGranted(['email:emails:viewown', 'email:emails:viewother'], 'MATCH_ONE')) {
            $event->addCommands(
                'mailvotech.email.emails',
                $this->emailModel->getCommandList()
            );
        }
    }
}
