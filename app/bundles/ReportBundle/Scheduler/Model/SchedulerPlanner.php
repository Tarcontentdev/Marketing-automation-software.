<?php

namespace MailVotech\ReportBundle\Scheduler\Model;

use Doctrine\ORM\EntityManagerInterface;
use MailVotech\ReportBundle\Entity\Report;
use MailVotech\ReportBundle\Entity\Scheduler;
use MailVotech\ReportBundle\Entity\SchedulerRepository;
use MailVotech\ReportBundle\Scheduler\Date\DateBuilder;
use MailVotech\ReportBundle\Scheduler\Exception\NoScheduleException;

class SchedulerPlanner
{
    public function __construct(
        private readonly DateBuilder $dateBuilder,
        private readonly EntityManagerInterface $entityManager,
        private readonly SchedulerRepository $schedulerRepository,
    ) {
    }

    public function computeScheduler(Report $report): void
    {
        $this->removeSchedulerOfReport($report);
        $this->planScheduler($report);
    }

    private function planScheduler(Report $report): void
    {
        try {
            $date = $this->dateBuilder->getNextEvent($report);
        } catch (NoScheduleException) {
            return;
        }

        $scheduler = new Scheduler($report, $date);
        $this->entityManager->persist($scheduler);
        $this->entityManager->flush();
    }

    private function removeSchedulerOfReport(Report $report): void
    {
        $scheduler = $this->schedulerRepository->getSchedulerByReport($report);
        if (!$scheduler) {
            return;
        }

        $this->entityManager->remove($scheduler);
        $this->entityManager->flush();
    }
}
