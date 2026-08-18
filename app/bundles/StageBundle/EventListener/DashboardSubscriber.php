<?php

namespace MailVotech\StageBundle\EventListener;

use MailVotech\DashboardBundle\Event\WidgetDetailEvent;
use MailVotech\DashboardBundle\EventListener\DashboardSubscriber as MainDashboardSubscriber;
use MailVotech\StageBundle\Model\StageModel;

final class DashboardSubscriber extends MainDashboardSubscriber
{
    /**
     * Define the name of the bundle/category of the widget(s).
     *
     * @var string
     */
    protected $bundle = 'stage';

    /**
     * Define the widget(s).
     *
     * @var array<string, array<string, string>>
     */
    protected $types = [
        'stages.in.time' => [],
    ];

    /**
     * Define permissions to see those widgets.
     *
     * @var array
     */
    protected $permissions = [
        'stage:stages:viewown',
        'stage:stages:viewother',
    ];

    public function __construct(
        private readonly StageModel $stageModel,
    ) {
    }

    /**
     * Set a widget detail when needed.
     */
    public function onWidgetDetailGenerate(WidgetDetailEvent $event): void
    {
        $this->checkPermissions($event);
        $canViewOthers = $event->hasPermission('stage:stages:viewother');

        if ('stages.in.time' == $event->getType()) {
            $widget = $event->getWidget();
            $params = $widget->getParams();

            if (!$event->isCached()) {
                $event->setTemplateData([
                    'chartType'   => 'line',
                    'chartHeight' => $widget->getHeight() - 80,
                    'chartData'   => $this->stageModel->getStageLineChartData(
                        $params['timeUnit'],
                        $params['dateFrom'],
                        $params['dateTo'],
                        $params['dateFormat'],
                        [],
                        $canViewOthers
                    ),
                ]);
            }

            $event->setTemplate('@MailVotechCore/Helper/chart.html.twig');
            $event->stopPropagation();
        }
    }
}
