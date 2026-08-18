<?php

namespace MailVotech\ReportBundle\Controller;

use MailVotech\CoreBundle\Controller\AjaxController as CommonAjaxController;
use MailVotech\CoreBundle\Service\FlashBag;
use MailVotech\ReportBundle\Model\ReportModel;
use MailVotech\ReportBundle\Scheduler\Date\DateBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Service\Attribute\Required;

final class ScheduleController extends CommonAjaxController
{
    private ReportModel $reportModel;

    #[Required]
    public function autowireScheduleController(
        ReportModel $reportModel,
    ): void {
        $this->reportModel = $reportModel;
    }

    public function indexAction(DateBuilder $dateBuilder, $isScheduled, $scheduleUnit, $scheduleDay, $scheduleMonthFrequency): JsonResponse
    {
        $dates = $dateBuilder->getPreviewDays($isScheduled, $scheduleUnit, $scheduleDay, $scheduleMonthFrequency);

        $html = $this->render(
            '@MailVotechReport/Schedule/index.html.twig',
            [
                'dates' => $dates,
            ]
        )->getContent();

        return $this->sendJsonResponse(
            [
                'html' => $html,
            ]
        );
    }

    /**
     * Sets report to schedule NOW if possible.
     *
     * @param int $reportId
     */
    public function nowAction($reportId): JsonResponse
    {
        /** @var \MailVotech\ReportBundle\Entity\Report $report */
        $report = $this->reportModel->getEntity($reportId);

        $security = $this->security;

        if (empty($report)) {
            $this->addFlashMessage('mailvotech.report.notfound', ['%id%' => $reportId], FlashBag::LEVEL_ERROR, 'messages');

            return $this->flushFlash();
        }

        if (!$security->hasEntityAccess('report:reports:viewown', 'report:reports:viewother', $report->getCreatedBy())) {
            $this->addFlashMessage('mailvotech.core.error.accessdenied', [], FlashBag::LEVEL_ERROR);

            return $this->flushFlash();
        }

        if ($report->isScheduled()) {
            $this->addFlashMessage('mailvotech.report.scheduled.already', ['%id%' => $reportId], FlashBag::LEVEL_ERROR);

            return $this->flushFlash();
        }

        $report->setAsScheduledNow($this->user->getEmail());
        $this->reportModel->saveEntity($report);

        $this->addFlashMessage(
            'mailvotech.report.scheduled.to.now',
            ['%id%' => $reportId, '%email%' => $this->user->getEmail()]
        );

        return $this->flushFlash();
    }

    private function flushFlash(): JsonResponse
    {
        return new JsonResponse(['flashes' => $this->getFlashContent()]);
    }
}
