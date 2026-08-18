<?php

namespace MailVotech\LeadBundle\Segment\Stat;

use MailVotech\CampaignBundle\Model\CampaignModel;
use MailVotech\EmailBundle\Model\EmailModel;
use MailVotech\FormBundle\Model\ActionModel;
use MailVotech\LeadBundle\Model\ListModel;
use MailVotech\PointBundle\Model\TriggerEventModel;
use MailVotech\ReportBundle\Model\ReportModel;

final readonly class SegmentDependencies
{
    public function __construct(
        private EmailModel $emailModel,
        private CampaignModel $campaignModel,
        private ActionModel $actionModel,
        private ListModel $listModel,
        private TriggerEventModel $triggerEventModel,
        private ReportModel $reportModel,
    ) {
    }

    public function getChannelsIds($segmentId): array
    {
        return [
            [
                'label' => 'mailvotech.email.emails',
                'route' => 'mailvotech_email_index',
                'ids'   => $this->emailModel->getEmailsIdsWithDependenciesOnSegment($segmentId),
            ], [
                'label' => 'mailvotech.campaign.campaigns',
                'route' => 'mailvotech_campaign_index',
                'ids'   => $this->campaignModel->getCampaignIdsWithDependenciesOnSegment($segmentId),
            ], [
                'label' => 'mailvotech.lead.lead.lists',
                'route' => 'mailvotech_segment_index',
                'ids'   => $this->listModel->getSegmentsWithDependenciesOnSegment($segmentId, 'id'),
            ], [
                'label' => 'mailvotech.report.reports',
                'route' => 'mailvotech_report_index',
                'ids'   => $this->reportModel->getReportsIdsWithDependenciesOnSegment($segmentId),
            ], [
                'label' => 'mailvotech.form.forms',
                'route' => 'mailvotech_form_index',
                'ids'   => $this->actionModel->getFormsIdsWithDependenciesOnSegment($segmentId),
            ], [
                'label' => 'mailvotech.point.trigger.header.index',
                'route' => 'mailvotech_pointtrigger_index',
                'ids'   => $this->triggerEventModel->getReportIdsWithDependenciesOnSegment($segmentId),
            ],
        ];
    }
}
