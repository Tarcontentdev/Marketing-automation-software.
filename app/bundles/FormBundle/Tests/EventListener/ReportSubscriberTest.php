<?php

declare(strict_types=1);

namespace MailVotech\FormBundle\Tests\EventListener;

use Doctrine\DBAL\Query\QueryBuilder;
use MailVotech\ChannelBundle\Helper\ChannelListHelper;
use MailVotech\CoreBundle\Helper\Chart\ChartQuery;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Test\AbstractMailVotechTestCase;
use MailVotech\CoreBundle\Translation\Translator;
use MailVotech\FormBundle\Entity\Field;
use MailVotech\FormBundle\Entity\Form;
use MailVotech\FormBundle\Entity\FormRepository;
use MailVotech\FormBundle\Entity\SubmissionRepository;
use MailVotech\FormBundle\EventListener\ReportSubscriber;
use MailVotech\FormBundle\Model\FormModel;
use MailVotech\LeadBundle\Model\CompanyReportData;
use MailVotech\LeadBundle\Report\DncReportService;
use MailVotech\ReportBundle\Event\ReportBuilderEvent;
use MailVotech\ReportBundle\Event\ReportGeneratorEvent;
use MailVotech\ReportBundle\Event\ReportGraphEvent;
use MailVotech\ReportBundle\Helper\ReportHelper;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ReportSubscriberTest extends AbstractMailVotechTestCase
{
    /**
     * @var MockObject&CompanyReportData
     */
    private MockObject $companyReportData;

    /**
     * @var MockObject&SubmissionRepository
     */
    private MockObject $submissionRepository;

    /**
     * @var MockObject&FormModel
     */
    private MockObject $formModel;

    /**
     * @var MockObject&FormRepository
     */
    private MockObject $formRepository;

    private ReportHelper $reportHelper;

    private ReportSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->configParams['form_results_data_sources'] = true;

        parent::setUp();

        $this->companyReportData    = $this->createMock(CompanyReportData::class);
        $this->submissionRepository = $this->createMock(SubmissionRepository::class);
        $this->formModel            = $this->createMock(FormModel::class);
        $this->formRepository       = $this->createMock(FormRepository::class);
        $this->reportHelper         = new ReportHelper($this->createStub(EventDispatcher::class));
        $this->subscriber           = new ReportSubscriber(
            $this->companyReportData,
            $this->submissionRepository,
            $this->formModel,
            $this->reportHelper,
            $this->createStub(CoreParametersHelper::class),
            $this->createStub(TranslatorInterface::class),
            $this->createStub(DncReportService::class),
            $this->formRepository
        );
    }

    public function testOnReportBuilderAddsFormAndFormSubmissionReports(): void
    {
        $mockEvent = $this->getMockBuilder(ReportBuilderEvent::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'checkContext',
                'addGraph',
                'getStandardColumns',
                'getCategoryColumns',
                'getCampaignByChannelColumns',
                'getLeadColumns',
                'getIpColumn',
                'addTable',
            ])
            ->getMock();

        $mockEvent->expects($this->once())
            ->method('getStandardColumns')
            ->willReturn([]);

        $mockEvent->expects($this->once())
            ->method('getCategoryColumns')
            ->willReturn([]);

        $mockEvent->expects($this->once())
            ->method('getCampaignByChannelColumns')
            ->willReturn([]);

        $mockEvent->expects($this->once())
            ->method('getLeadColumns')
            ->willReturn([]);

        $mockEvent->expects($this->once())
            ->method('getIpColumn')
            ->willReturn([]);

        $mockEvent->expects($this->exactly(3))
            ->method('checkContext')
            ->willReturnOnConsecutiveCalls(true, true, false);

        $setTables = [];
        $setGraphs = [];

        $mockEvent->expects($this->exactly(2))
            ->method('addTable')
            ->willReturnCallback(function () use ($mockEvent, &$setTables): ReportBuilderEvent {
                $args = func_get_args();

                $setTables[] = $args;

                return $mockEvent;
            });

        $mockEvent->expects($this->exactly(3))
            ->method('addGraph')
            ->willReturnCallback(function () use ($mockEvent, &$setGraphs): ReportBuilderEvent {
                $args = func_get_args();

                $setGraphs[] = $args;

                return $mockEvent;
            });

        $this->companyReportData->expects($this->once())
            ->method('getCompanyData')
            ->with()
            ->willReturn([]);

        $this->subscriber->onReportBuilder($mockEvent);

        $this->assertCount(2, $setTables);
        $this->assertCount(3, $setGraphs);
    }

    public function testOnReportBuilderWithWrongContext(): void
    {
        $reportBuilderEvent = new ReportBuilderEvent(
            $this->createStub(TranslatorInterface::class),
            $this->createStub(ChannelListHelper::class),
            'test',
            [],
            $this->reportHelper,
            ''
        );

        $this->subscriber->onReportBuilder($reportBuilderEvent);

        $this->assertCount(0, $reportBuilderEvent->getTables());
    }

    public function testOnReportBuilderAddsFormAndFormResultReports(): void
    {
        $reportBuilderEvent = new ReportBuilderEvent(
            $this->createStub(TranslatorInterface::class),
            $this->createStub(ChannelListHelper::class),
            ReportSubscriber::CONTEXT_FORM_RESULT,
            [],
            $this->reportHelper,
            ''
        );

        $field = new Field();
        $field->setAlias('email');
        $field->setType('string');
        $field->setLabel('Email');
        $field->setMappedObject('contact');
        $field->setMappedField('email');

        $form = new Form();
        $form->addField('email', $field);
        $field->setForm($form);

        $this->formModel->expects($this->once())
            ->method('getCustomComponents')
            ->willReturn(['viewOnlyFields' => ['button', 'captcha', 'freetext', 'freehtml', 'pagebreak', 'plugin.loginSocial']]);

        $this->formRepository->expects($this->once())
            ->method('getEntities')
            ->willReturn([[$form, 1]]);

        $this->formRepository->expects($this->once())
            ->method('getResultsTableName')
            ->willReturn('test');

        $this->subscriber->onReportBuilder($reportBuilderEvent);

        $tables = $reportBuilderEvent->getTables();

        $this->assertCount(2, $tables);
        $this->assertArrayHasKey('form.results.test', $tables);
        $this->assertCount(3, $tables['form.results.test']['columns']);
    }

    public function testOnReportGenerateFormsContext(): void
    {
        $mockQueryBuilder = $this->createMock(QueryBuilder::class);
        $mockEvent        = $this->getMockBuilder(ReportGeneratorEvent::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getContext',
                'getQueryBuilder',
                'addCategoryLeftJoin',
                'setQueryBuilder',
            ])
            ->getMock();

        $mockQueryBuilder->expects($this->once())
            ->method('from')
            ->willReturn($mockQueryBuilder);

        $mockEvent->expects($this->once())
            ->method('getQueryBuilder')
            ->willReturn($mockQueryBuilder);

        $mockEvent->expects($this->once())
            ->method('getContext')
            ->willReturn('forms');

        $this->subscriber->onReportGenerate($mockEvent);
    }

    public function testOnReportGenerateFormSubmissionContext(): void
    {
        $mockQueryBuilder = $this->createMock(QueryBuilder::class);
        $mockEvent        = $this->getMockBuilder(ReportGeneratorEvent::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getContext',
                'getQueryBuilder',
                'addCategoryLeftJoin',
                'addIpAddressLeftJoin',
                'addLeadLeftJoin',
                'addCampaignByChannelJoin',
                'applyDateFilters',
                'setQueryBuilder',
            ])
            ->getMock();

        $mockQueryBuilder->expects($this->once())
            ->method('from')
            ->willReturn($mockQueryBuilder);

        $mockQueryBuilder->expects($this->exactly(2))
            ->method('leftJoin')
            ->willReturn($mockQueryBuilder);

        $mockEvent->expects($this->once())
            ->method('getQueryBuilder')
            ->willReturn($mockQueryBuilder);

        $mockEvent->expects($this->once())
            ->method('getContext')
            ->willReturn('form.submissions');

        $this->subscriber->onReportGenerate($mockEvent);
    }

    public function testOnReportGenerateFormResultsContext(): void
    {
        $mockQueryBuilder = $this->createMock(QueryBuilder::class);
        $mockEvent        = $this->getMockBuilder(ReportGeneratorEvent::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getContext',
                'getQueryBuilder',
                'addLeadLeftJoin',
                'setQueryBuilder',
            ])
            ->getMock();

        $mockQueryBuilder->expects($this->once())
            ->method('from')
            ->willReturn($mockQueryBuilder);

        $mockQueryBuilder->expects($this->once())
            ->method('leftJoin')
            ->willReturn($mockQueryBuilder);

        $mockEvent->expects($this->once())
            ->method('getQueryBuilder')
            ->willReturn($mockQueryBuilder);

        $mockEvent->expects($this->once())
            ->method('getContext')
            ->willReturn('form.results');

        $this->subscriber->onReportGenerate($mockEvent);
    }

    public function testOnReportGraphGenerateBadContextWillReturn(): void
    {
        $mockEvent = $this->createMock(ReportGraphEvent::class);

        $mockEvent->expects($this->once())
            ->method('checkContext')
            ->willReturn(false);

        $mockEvent->expects($this->never())
            ->method('getRequestedGraphs');

        $this->subscriber->onReportGraphGenerate($mockEvent);
    }

    public function testOnReportGraphGenerate(): void
    {
        $mockEvent        = $this->createMock(ReportGraphEvent::class);
        $mockTrans        = $this->createMock(Translator::class);
        $mockQueryBuilder = $this->createStub(QueryBuilder::class);
        $mockChartQuery   = $this->createMock(ChartQuery::class);

        $mockTrans
            ->method('trans')
            ->willReturnArgument(0);

        $mockEvent->expects($this->once())
            ->method('getQueryBuilder')
            ->willReturn($mockQueryBuilder);

        $mockChartQuery
            ->method('loadAndBuildTimeData')
            ->willReturn(['a', 'b', 'c']);

        $mockChartQuery
            ->method('fetchCount')
            ->willReturn(2);

        $mockChartQuery
            ->method('fetchCountDateDiff')
            ->willReturn(2);

        $graphOptions = [
            'chartQuery' => $mockChartQuery,
            'translator' => $mockTrans,
            'dateFrom'   => new \DateTime(),
            'dateTo'     => new \DateTime(),
        ];

        $mockEvent->expects($this->once())
            ->method('checkContext')
            ->willReturn(true);

        $mockEvent
            ->method('getOptions')
            ->willReturn($graphOptions);

        $mockEvent->expects($this->once())
            ->method('getRequestedGraphs')
            ->willReturn(
                [
                    'mailvotech.form.graph.line.submissions',
                    'mailvotech.form.table.top.referrers',
                    'mailvotech.form.table.most.submitted',
                ]
            );

        $this->submissionRepository->expects($this->once())
            ->method('getTopReferrers')
            ->willReturn(['a', 'b', 'c']);

        $this->submissionRepository->expects($this->once())
            ->method('getMostSubmitted')
            ->willReturn(['a', 'b', 'c']);

        $this->subscriber->onReportGraphGenerate($mockEvent);
    }
}
