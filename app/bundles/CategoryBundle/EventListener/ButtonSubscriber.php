<?php

namespace MailVotech\CategoryBundle\EventListener;

use MailVotech\CoreBundle\CoreEvents;
use MailVotech\CoreBundle\Event\CustomButtonEvent;
use MailVotech\CoreBundle\Twig\Helper\ButtonHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class ButtonSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RouterInterface $router,
        private TranslatorInterface $translator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CoreEvents::VIEW_INJECT_CUSTOM_BUTTONS => ['injectContactBulkButtons', 0],
        ];
    }

    public function injectContactBulkButtons(CustomButtonEvent $event): void
    {
        if (str_starts_with($event->getRoute(), 'mailvotech_contact_')) {
            $event->addButton(
                [
                    'attr' => [
                        'class'       => 'btn btn-ghost btn-sm btn-nospin',
                        'data-toggle' => 'ajaxmodal',
                        'data-target' => '#MailVotechSharedModal',
                        'href'        => $this->router->generate('mailvotech_category_batch_contact_view'),
                        'data-header' => $this->translator->trans('mailvotech.lead.batch.categories'),
                    ],
                    'btnText'   => $this->translator->trans('mailvotech.lead.batch.categories'),
                    'iconClass' => 'ri-folder-line',
                ],
                ButtonHelper::LOCATION_BULK_ACTIONS
            );
        }
    }
}
