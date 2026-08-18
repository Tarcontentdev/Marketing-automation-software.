<?php

namespace MailVotech\PageBundle\EventListener;

use MailVotech\CampaignBundle\CampaignEvents;
use MailVotech\CampaignBundle\Event\CampaignBuilderEvent;
use MailVotech\CampaignBundle\Event\CampaignExecutionEvent;
use MailVotech\CampaignBundle\Executioner\RealTimeExecutioner;
use MailVotech\EmailBundle\Helper\UrlMatcher;
use MailVotech\LeadBundle\Form\Type\CampaignEventLeadDeviceType;
use MailVotech\LeadBundle\Model\LeadModel;
use MailVotech\PageBundle\Entity\Hit;
use MailVotech\PageBundle\Entity\Page;
use MailVotech\PageBundle\Event\PageHitEvent;
use MailVotech\PageBundle\Form\Type\CampaignEventPageHitType;
use MailVotech\PageBundle\Form\Type\TrackingPixelSendType;
use MailVotech\PageBundle\Helper\TrackingHelper;
use MailVotech\PageBundle\PageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class CampaignSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LeadModel $leadModel,
        private TrackingHelper $trackingHelper,
        private RealTimeExecutioner $realTimeExecutioner,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CampaignEvents::CAMPAIGN_ON_BUILD        => ['onCampaignBuild', 0],
            PageEvents::PAGE_ON_HIT                  => ['onPageHit', 0],
            PageEvents::ON_CAMPAIGN_TRIGGER_DECISION => [
                ['onCampaignTriggerDecision', 0],
                ['onCampaignTriggerDecisionDeviceHit', 1],
            ],
            PageEvents::ON_CAMPAIGN_TRIGGER_ACTION => ['onCampaignTriggerAction', 0],
        ];
    }

    /**
     * Add event triggers and actions.
     */
    public function onCampaignBuild(CampaignBuilderEvent $event): void
    {
        // Add trigger
        $pageHitTrigger = [
            'label'          => 'mailvotech.page.campaign.event.pagehit',
            'description'    => 'mailvotech.page.campaign.event.pagehit_descr',
            'formType'       => CampaignEventPageHitType::class,
            'eventName'      => PageEvents::ON_CAMPAIGN_TRIGGER_DECISION,
            'channel'        => 'page',
            'channelIdField' => 'pages',
        ];
        $event->addDecision('page.pagehit', $pageHitTrigger);

        // Add trigger
        $deviceHitTrigger = [
            'label'          => 'mailvotech.page.campaign.event.devicehit',
            'description'    => 'mailvotech.page.campaign.event.devicehit_descr',
            'formType'       => CampaignEventLeadDeviceType::class,
            'eventName'      => PageEvents::ON_CAMPAIGN_TRIGGER_DECISION,
            'channel'        => 'page',
            'channelIdField' => 'pages',
        ];
        $event->addDecision('page.devicehit', $deviceHitTrigger);

        $trackingServices = $this->trackingHelper->getEnabledServices();
        if ([] !== $trackingServices) {
            $action = [
                'label'                  => 'mailvotech.page.tracking.pixel.event.send',
                'description'            => 'mailvotech.page.tracking.pixel.event.send_desc',
                'eventName'              => PageEvents::ON_CAMPAIGN_TRIGGER_ACTION,
                'formType'               => TrackingPixelSendType::class,
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
            $event->addAction('tracking.pixel.send', $action);
        }
    }

    /**
     * Trigger actions for page hits.
     */
    public function onPageHit(PageHitEvent $event): void
    {
        $hit       = $event->getHit();
        $channel   = 'page';
        $channelId = null;
        if ($redirect = $hit->getRedirect()) {
            $channel   = 'page.redirect';
            $channelId = $redirect->getId();
        } elseif ($page = $hit->getPage()) {
            $channelId = $page->getId();
        }
        $this->realTimeExecutioner->execute('page.pagehit', $hit, $channel, $channelId);
        $this->realTimeExecutioner->execute('page.devicehit', $hit, $channel, $channelId);
    }

    public function onCampaignTriggerDecisionDeviceHit(CampaignExecutionEvent $event): false|CampaignExecutionEvent
    {
        $eventDetails = $event->getEventDetails();
        $config       = $event->getConfig();
        $lead         = $event->getLead();

        if (!$event->checkContext('page.devicehit')) {
            return false;
        }

        if (!$eventDetails instanceof Hit) {
            return $event->setResult(false);
        }

        $deviceRepo = $this->leadModel->getDeviceRepository();
        $result     = false;

        $deviceId     = $eventDetails->getDeviceStat() ? $eventDetails->getDeviceStat()->getId() : null;
        $deviceType   = $config['device_type'];
        $deviceBrands = $config['device_brand'];
        $deviceOs     = $config['device_os'];

        if (!empty($deviceType)) {
            $result = false;
            if (!empty($deviceRepo->getDevice($lead, $deviceType, null, null, null, $deviceId))) {
                $result = true;
            }
        }

        if (!empty($deviceBrands)) {
            $result = false;
            if (!empty($deviceRepo->getDevice($lead, null, $deviceBrands, null, null, $deviceId))) {
                $result = true;
            }
        }

        if (!empty($deviceOs)) {
            $result = false;
            if (!empty($deviceRepo->getDevice($lead, null, null, null, $deviceOs, $deviceId))) {
                $result = true;
            }
        }

        return $event->setResult($result);
    }

    public function onCampaignTriggerDecision(CampaignExecutionEvent $event): bool|CampaignExecutionEvent
    {
        $eventDetails = $event->getEventDetails();
        $config       = $event->getConfig();

        if (!$event->checkContext('page.pagehit')) {
            return false;
        }

        if (null == $eventDetails) {
            return true;
        }

        if (!$eventDetails instanceof Hit) {
            return $event->setResult(false);
        }

        $pageHit = $eventDetails->getPage();

        // Check Landing Pages
        if ($pageHit instanceof Page) {
            [$parent, $children] = $pageHit->getVariants();
            // use the parent (self or configured parent)
            $pageHitId = $parent->getId();
        } else {
            $pageHitId = 0;
        }

        $limitToPages    = $config['pages'] ?? [];
        $pageUrl         = null;
        $limitToUrls     = [];
        $refererUrl      = null;
        $limitToReferers = [];

        // Check Landing Pages URL or Tracing Pixel URL
        if (isset($config['url']) && $config['url']) {
            $pageUrl     = html_entity_decode($eventDetails->getUrl());
            $limitToUrls = explode(',', $config['url']);
            $limitToUrls = \array_map(static fn (string $url): string => \html_entity_decode(\trim($url)), $limitToUrls);
        }

        // Check Landing Pages URL or Tracing Pixel URL
        if (isset($config['referer']) && $config['referer']) {
            $refererUrl      = html_entity_decode($eventDetails->getReferer());
            $limitToReferers = explode(',', $config['referer']);
            $limitToReferers = \array_map(static fn (string $referer): string => \html_entity_decode(\trim($referer)), $limitToReferers);
        }

        // **Page hit is true if:**
        // 1. no landing page is set and no URL rule is set
        $applyToAny = (empty($config['url']) && empty($config['referer']) && empty($limitToPages));

        // 2. some landing pages are set and page ID match
        $langingPageIsHit = (!empty($limitToPages) && in_array($pageHitId, $limitToPages));

        // 3. URL rule is set and match with URL hit
        $urlIsHit = (!empty($config['url']) && UrlMatcher::hasMatch($limitToUrls, $pageUrl));

        // 3. URL rule is set and match with URL hit
        $refererIsHit = (!empty($config['referer']) && UrlMatcher::hasMatch($limitToReferers, $refererUrl));

        if ($applyToAny || $langingPageIsHit || $urlIsHit || $refererIsHit) {
            return $event->setResult(true);
        }

        return $event->setResult(false);
    }

    public function onCampaignTriggerAction(CampaignExecutionEvent $event): void
    {
        $config = $event->getConfig();
        if (empty($config['services'])) {
            $event->setResult(false);

            return;
        }

        $values = [];
        foreach ($config['services'] as $service) {
            $values[$service][] = ['category' => $config['category'], 'action' => $config['action'], 'label' => $config['label']];
        }
        $this->trackingHelper->updateCacheItem($values);

        $event->setResult(true);
    }
}
