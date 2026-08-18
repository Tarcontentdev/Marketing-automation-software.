<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechTagManagerBundle\Stats;

use MailVotech\CampaignBundle\Model\CampaignModel;
use MailVotech\FormBundle\Model\ActionModel;
use MailVotech\LeadBundle\Entity\Tag;
use MailVotech\LeadBundle\Model\ListModel;
use MailVotech\PointBundle\Model\TriggerEventModel;
use MailVotech\ReportBundle\Model\ReportModel;

final readonly class TagDependencies
{
    public function __construct(
        private CampaignModel $campaignModel,
        private ListModel $listModel,
        private ActionModel $actionModel,
        private TriggerEventModel $triggerEventModel,
        private ReportModel $reportModel,
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getChannelsIds(Tag $tag): array
    {
        return [
            [
                'label' => 'mailvotech.campaign.campaigns',
                'route' => 'mailvotech_campaign_index',
                'ids'   => $this->campaignModel->getCampaignIdsWithDependenciesOnTagName($tag->getTag()),
            ],
            [
                'label' => 'mailvotech.lead.lead.lists',
                'route' => 'mailvotech_segment_index',
                'ids'   => $this->listModel->getSegmentIdsWithDependenciesOnTag($tag->getId()),
            ],
            [
                'label' => 'mailvotech.form.forms',
                'route' => 'mailvotech_form_index',
                'ids'   => $this->actionModel->getFormsIdsWithDependenciesOnTag($tag->getTag()),
            ],
            [
                'label' => 'mailvotech.point.trigger.header.index',
                'route' => 'mailvotech_pointtrigger_index',
                'ids'   => $this->triggerEventModel->getPointTriggerIdsWithDependenciesOnTag($tag->getTag()),
            ],
            [
                'label' => 'mailvotech.report.reports',
                'route' => 'mailvotech_report_index',
                'ids'   => $this->reportModel->getReportsIdsWithDependenciesOnTag($tag->getId()),
            ],
        ];
    }
}
