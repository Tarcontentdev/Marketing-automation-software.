<?php

declare(strict_types=1);

namespace MailVotechPlugin\GrapesJsBuilderBundle\EventSubscriber;

use MailVotech\PageBundle\Event\PageEvent;
use MailVotech\PageBundle\PageEvents;
use MailVotechPlugin\GrapesJsBuilderBundle\Integration\Config;
use MailVotechPlugin\GrapesJsBuilderBundle\Model\GrapesJsBuilderModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class PageSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Config $config,
        private GrapesJsBuilderModel $grapesJsBuilderModel,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PageEvents::PAGE_POST_SAVE => ['onPagePostSave', 0],
        ];
    }

    public function onPagePostSave(PageEvent $event): void
    {
        if (!$this->config->isPublished()) {
            return;
        }

        $this->grapesJsBuilderModel->addOrEditPageEntity($event->getPage());
    }
}
