<?php

namespace MailVotechPlugin\MailVotechFullContactBundle\EventListener;

use MailVotech\CoreBundle\CoreEvents;
use MailVotech\CoreBundle\Event\CustomButtonEvent;
use MailVotech\CoreBundle\Twig\Helper\ButtonHelper;
use MailVotech\PluginBundle\Helper\IntegrationHelper;
use MailVotechPlugin\MailVotechFullContactBundle\Integration\FullContactIntegration;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class ButtonSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private IntegrationHelper $helper,
        private TranslatorInterface $translator,
        private RouterInterface $router,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CoreEvents::VIEW_INJECT_CUSTOM_BUTTONS => ['injectViewButtons', 0],
        ];
    }

    public function injectViewButtons(CustomButtonEvent $event): void
    {
        // get api_key from plugin settings
        /** @var FullContactIntegration $myIntegration */
        $myIntegration = $this->helper->getIntegrationObject('FullContact');

        if (false === $myIntegration || !$myIntegration->getIntegrationSettings()->getIsPublished()) {
            return;
        }

        if (str_starts_with($event->getRoute(), 'mailvotech_contact_')) {
            $event->addButton(
                [
                    'attr' => [
                        'class'       => 'btn btn-ghost btn-sm btn-nospin',
                        'data-toggle' => 'ajaxmodal',
                        'data-target' => '#MailVotechSharedModal',
                        'onclick'     => 'this.href=\''.
                            $this->router->generate(
                                'mailvotech_plugin_fullcontact_action',
                                ['objectAction' => 'batchLookupPerson']
                            ).
                            '?\' + mQuery.param({\'fullcontact_batch_lookup\':{\'ids\':JSON.parse(MailVotech.getCheckedListIds(false, true))}});return true;',
                        'data-header' => $this->translator->trans('mailvotech.plugin.fullcontact.button.caption'),
                    ],
                    'btnText'   => $this->translator->trans('mailvotech.plugin.fullcontact.button.caption'),
                    'iconClass' => 'ri-search-line',
                ],
                ButtonHelper::LOCATION_BULK_ACTIONS
            );

            if ($event->getItem()) {
                $lookupContactButton = [
                    'attr' => [
                        'data-toggle' => 'ajaxmodal',
                        'data-target' => '#MailVotechSharedModal',
                        'data-header' => $this->translator->trans(
                            'mailvotech.plugin.fullcontact.lookup.header',
                            ['%item%' => $event->getItem()->getEmail()]
                        ),
                        'href' => $this->router->generate(
                            'mailvotech_plugin_fullcontact_action',
                            ['objectId' => $event->getItem()->getId(), 'objectAction' => 'lookupPerson']
                        ),
                    ],
                    'btnText'   => $this->translator->trans('mailvotech.plugin.fullcontact.button.caption'),
                    'iconClass' => 'ri-search-line',
                ];

                $event
                    ->addButton(
                        $lookupContactButton,
                        ButtonHelper::LOCATION_PAGE_ACTIONS,
                        ['mailvotech_contact_action', ['objectAction' => 'view']]
                    )
                    ->addButton(
                        $lookupContactButton,
                        ButtonHelper::LOCATION_LIST_ACTIONS,
                        'mailvotech_contact_index'
                    );
            }
        } else {
            if (str_starts_with($event->getRoute(), 'mailvotech_company_')) {
                $event->addButton(
                    [
                        'attr' => [
                            'class'       => 'btn btn-ghost btn-sm btn-nospin',
                            'data-toggle' => 'ajaxmodal',
                            'data-target' => '#MailVotechSharedModal',
                            'onclick'     => 'this.href=\''.
                                $this->router->generate(
                                    'mailvotech_plugin_fullcontact_action',
                                    ['objectAction' => 'batchLookupCompany']
                                ).
                                '?\' + mQuery.param({\'fullcontact_batch_lookup\':{\'ids\':JSON.parse(MailVotech.getCheckedListIds(false, true))}});return true;',
                            'data-header' => $this->translator->trans(
                                'mailvotech.plugin.fullcontact.button.caption'
                            ),
                        ],
                        'btnText'   => $this->translator->trans('mailvotech.plugin.fullcontact.button.caption'),
                        'iconClass' => 'ri-search-line',
                    ],
                    ButtonHelper::LOCATION_BULK_ACTIONS
                );

                if ($event->getItem()) {
                    $lookupCompanyButton = [
                        'attr' => [
                            'data-toggle' => 'ajaxmodal',
                            'data-target' => '#MailVotechSharedModal',
                            'data-header' => $this->translator->trans(
                                'mailvotech.plugin.fullcontact.lookup.header',
                                ['%item%' => $event->getItem()->getName()]
                            ),
                            'href' => $this->router->generate(
                                'mailvotech_plugin_fullcontact_action',
                                ['objectId' => $event->getItem()->getId(), 'objectAction' => 'lookupCompany']
                            ),
                        ],
                        'btnText'   => $this->translator->trans('mailvotech.plugin.fullcontact.button.caption'),
                        'iconClass' => 'ri-search-line',
                    ];

                    $event
                        ->addButton(
                            $lookupCompanyButton,
                            ButtonHelper::LOCATION_LIST_ACTIONS,
                            'mailvotech_company_index'
                        );
                }
            }
        }
    }
}
