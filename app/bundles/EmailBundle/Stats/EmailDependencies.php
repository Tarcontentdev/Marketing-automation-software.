<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\Stats;

use MailVotech\CampaignBundle\Model\CampaignModel;
use MailVotech\FormBundle\Model\ActionModel;
use MailVotech\LeadBundle\Model\ListModel;
use MailVotech\PointBundle\Model\PointModel;
use MailVotech\PointBundle\Model\TriggerEventModel;
use MailVotech\ReportBundle\Model\ReportModel;

final readonly class EmailDependencies
{
    public function __construct(
        private CampaignModel $campaignModel,
        private ListModel $listModel,
        private ActionModel $actionModel,
        private PointModel $pointModel,
        private TriggerEventModel $triggerEventModel,
        private ReportModel $reportModel,
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getChannelsIds(int $emailId): array
    {
        return [
            [
                'label' => 'mailvotech.campaign.campaigns',
                'route' => 'mailvotech_campaign_index',
                'ids'   => $this->campaignModel->getCampaignIdsWithDependenciesOnEmail($emailId),
            ],
            [
                'label' => 'mailvotech.lead.lead.lists',
                'route' => 'mailvotech_segment_index',
                'ids'   => $this->listModel->getSegmentIdsWithDependenciesOnEmail($emailId),
            ],
            [
                'label' => 'mailvotech.form.forms',
                'route' => 'mailvotech_form_index',
                'ids'   => $this->actionModel->getFormsIdsWithDependenciesOnEmail($emailId),
            ],
            [
                'label' => 'mailvotech.point.actions.header.index',
                'route' => 'mailvotech_point_index',
                'ids'   => $this->pointModel->getPointActionIdsWithDependenciesOnEmail($emailId),
            ],
            [
                'label' => 'mailvotech.point.trigger.header.index',
                'route' => 'mailvotech_pointtrigger_index',
                'ids'   => $this->triggerEventModel->getPointTriggerIdsWithDependenciesOnEmail($emailId),
            ],
            [
                'label' => 'mailvotech.report.reports',
                'route' => 'mailvotech_report_index',
                'ids'   => $this->reportModel->getReportsIdsWithDependenciesOnEmail($emailId),
            ],
        ];
    }
}
