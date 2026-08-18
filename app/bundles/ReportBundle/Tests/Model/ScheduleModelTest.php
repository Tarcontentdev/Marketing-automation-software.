<?php

declare(strict_types=1);

namespace MailVotech\ReportBundle\Tests\Model;

use Doctrine\ORM\EntityManager;
use MailVotech\ReportBundle\Entity\Report;
use MailVotech\ReportBundle\Entity\SchedulerRepository;
use MailVotech\ReportBundle\Model\ScheduleModel;
use MailVotech\ReportBundle\Scheduler\Model\SchedulerPlanner;
use MailVotech\ReportBundle\Scheduler\Option\ExportOption;
use PHPUnit\Framework\MockObject\MockObject;

final class ScheduleModelTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MockObject&SchedulerRepository
     */
    private MockObject $schedulerRepository;

    /**
     * @var MockObject&EntityManager
     */
    private MockObject $entityManager;

    /**
     * @var MockObject&SchedulerPlanner
     */
    private MockObject $schedulerPlanner;

    private ScheduleModel $scheduleModel;

    protected function setUp(): void
    {
        $this->schedulerRepository = $this->createMock(SchedulerRepository::class);
        $this->entityManager       = $this->createMock(EntityManager::class);
        $this->schedulerPlanner    = $this->createMock(SchedulerPlanner::class);

        $this->scheduleModel = new ScheduleModel(
            $this->entityManager,
            $this->schedulerPlanner,
            $this->schedulerRepository,
        );
    }

    public function testGetScheduledReportsForExport(): void
    {
        $this->schedulerRepository->expects($this->once())
            ->method('getScheduledReportsForExport')
            ->with($this->createStub(ExportOption::class));

        $this->scheduleModel->getScheduledReportsForExport($this->createStub(ExportOption::class));
    }

    public function testReportWasScheduled(): void
    {
        $report = new Report();

        $this->schedulerPlanner->expects($this->once())
            ->method('computeScheduler')
            ->with($report);

        $this->scheduleModel->reportWasScheduled($report);
    }

    public function testTurnOffScheduler(): void
    {
        $report = new Report();

        $report->setIsScheduled(true);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($report);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->scheduleModel->turnOffScheduler($report);

        $this->assertFalse($report->isScheduled());
    }
}
