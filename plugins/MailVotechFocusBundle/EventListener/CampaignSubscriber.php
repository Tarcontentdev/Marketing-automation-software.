<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechFocusBundle\EventListener;

use MailVotech\CampaignBundle\CampaignEvents;
use MailVotech\CampaignBundle\Event\CampaignBuilderEvent;
use MailVotech\CampaignBundle\Event\CampaignExecutionEvent;
use MailVotech\PageBundle\Helper\TrackingHelper;
use MailVotechPlugin\MailVotechFocusBundle\FocusEvents;
use MailVotechPlugin\MailVotechFocusBundle\Form\Type\FocusShowType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

final readonly class CampaignSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private TrackingHelper $trackingHelper,
        private RouterInterface $router,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CampaignEvents::CAMPAIGN_ON_BUILD       => ['onCampaignBuild', 0],
            FocusEvents::ON_CAMPAIGN_TRIGGER_ACTION => ['onCampaignTriggerAction', 0],
        ];
    }

    public function onCampaignBuild(CampaignBuilderEvent $event): void
    {
        $action = [
            'label'                  => 'mailvotech.focus.campaign.event.show_focus',
            'description'            => 'mailvotech.focus.campaign.event.show_focus_descr',
            'eventName'              => FocusEvents::ON_CAMPAIGN_TRIGGER_ACTION,
            'formType'               => FocusShowType::class,
            'formTheme'              => '@MailVotechFocus/FormTheme/FocusShowList/focusshow_list_row.html.twig',
            'formTypeOptions'        => ['update_select' => 'campaignevent_properties_focus'],
            'connectionRestrictions' => [
                'anchor' => [
                    'decision.inaction',
                ],
                'source' => [
                    'decision' => [
                        'page.pagehit',
                    ],
                ],
            ],
        ];
        $event->addAction('focus.show', $action);
    }

    public function onCampaignTriggerAction(CampaignExecutionEvent $event): void
    {
        $focusId = (int) $event->getConfig()['focus'];
        if (!$focusId) {
            $event->setResult(false);

            return;
        }
        $values                 = [];
        $values['focus_item'][] = ['id' => $focusId, 'js' => $this->router->generate('mailvotech_focus_generate', ['id' => $focusId], UrlGeneratorInterface::ABSOLUTE_URL)];
        $this->trackingHelper->updateCacheItem($values);

        $event->setResult(true);
    }
}
