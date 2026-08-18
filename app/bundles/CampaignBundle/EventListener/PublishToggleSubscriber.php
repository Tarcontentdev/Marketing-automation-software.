<?php

declare(strict_types=1);

namespace MailVotech\CampaignBundle\EventListener;

use MailVotech\CampaignBundle\Entity\Campaign;
use MailVotech\CoreBundle\CoreEvents;
use MailVotech\CoreBundle\Event\CustomTemplateEvent;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class PublishToggleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private CoreParametersHelper $coreParametersHelper,
        private TranslatorInterface $translator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CoreEvents::VIEW_INJECT_CUSTOM_TEMPLATE => ['onTemplateRender', 0],
        ];
    }

    public function onTemplateRender(CustomTemplateEvent $event): void
    {
        if ('@MailVotechCore/Helper/publishstatus_icon.html.twig' !== $event->getTemplate()) {
            return;
        }

        if (empty($event->getVars()['item']) || !$event->getVars()['item'] instanceof Campaign) {
            return;
        }

        $republishBehavior  = $event->getVars()['item']->getRepublishBehavior() ?? $this->coreParametersHelper->get('campaign_republish_behavior');
        $republishBehavior  = $this->translator->trans('mailvotech.campaignconfig.campaign_republish_behavior.'.$republishBehavior);
        $vars               = $event->getVars();
        $vars['onclick']    = 'MailVotech.confirmationCampaignPublishStatus(mQuery(this));';
        $vars['attributes'] = [
            'data-toggle'           => 'confirmation',
            'data-confirm-callback' => 'confirmCallbackCampaignPublishStatus',
            'data-cancel-callback'  => 'dismissConfirmation',
        ];
        $vars['transKeys'] = [
            'data-message-publish'   => $this->translator->trans('mailvotech.campaign.form.confirmation.message.publish', ['%republishBehavior%' => $republishBehavior]),
            'data-message-unpublish' => $this->translator->trans('mailvotech.campaign.form.confirmation.message'),
            'data-confirm-text'      => 'mailvotech.campaign.form.confirmation.confirm_text',
            'data-cancel-text'       => 'mailvotech.campaign.form.confirmation.cancel_text',
        ];

        $event->setVars($vars);
    }
}
