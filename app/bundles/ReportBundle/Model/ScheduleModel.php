<?php

declare(strict_types=1);

namespace MailVotech\ReportBundle\Model;

use Doctrine\ORM\EntityManagerInterface;
use MailVotech\ReportBundle\Entity\Report;
use MailVotech\ReportBundle\Entity\Scheduler;
use MailVotech\ReportBundle\Entity\SchedulerRepository;
use MailVotech\ReportBundle\Scheduler\Model\SchedulerPlanner;
use MailVotech\ReportBundle\Scheduler\Option\ExportOption;

class ScheduleModel
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SchedulerPlanner $schedulerPlanner,
        private readonly SchedulerRepository $schedulerRepository,
    ) {
    }

    /**
     * Avoid the default AbstractCommonModel::getRepository() as it caches it to a static property.
     */
    public function getRepository(): SchedulerRepository
    {
        return $this->schedulerRepository;
    }

    /**
     * @return Scheduler[]
     */
    public function getScheduledReportsForExport(ExportOption $exportOption)
    {
        return $this->schedulerRepository->getScheduledReportsForExport($exportOption);
    }

    public function reportWasScheduled(Report $report): void
    {
        $this->schedulerPlanner->computeScheduler($report);
    }

    public function turnOffScheduler(Report $report): void
    {
        $report->setIsScheduled(false);
        $this->entityManager->persist($report);
        $this->entityManager->flush();
    }
}
