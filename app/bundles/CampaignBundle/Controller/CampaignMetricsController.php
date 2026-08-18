<?php

declare(strict_types=1);

namespace MailVotech\CampaignBundle\Controller;

use MailVotech\CampaignBundle\Event\EventPreview;
use MailVotech\CampaignBundle\Model\CampaignModel;
use MailVotech\CampaignBundle\Model\EventModel;
use MailVotech\CoreBundle\Helper\Chart\BarChart;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Helper\DateTimeHelper;
use MailVotech\CoreBundle\Translation\Translator;
use MailVotech\EmailBundle\Stats\EmailPeriodMetrics;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class CampaignMetricsController extends AbstractController
{
    public function __construct(
        private readonly Translator $translator,
        private readonly CoreParametersHelper $coreParametersHelper,
    ) {
    }

    public function emailWeekdaysAction(
        EmailPeriodMetrics $emailPeriodMetrics,
        CampaignModel $model,
        int $objectId,
        string $dateFrom = '',
        string $dateTo = '',
    ): Response {
        $entity               = $model->getEntity($objectId);
        $eventsIds            = $entity->getEmailSendEvents()->getKeys();
        $dateFromObject       = new \DateTimeImmutable($dateFrom);
        $dateToObject         = new \DateTimeImmutable($dateTo);

        $dateTimeHelper        = new DateTimeHelper();
        $defaultTimezoneOffset = $dateTimeHelper->getLocalDateTime()->format('Z');
        $stats                 = $emailPeriodMetrics->emailMetricsPerWeekdayByCampaignEvents($eventsIds, $dateFromObject, $dateToObject, $defaultTimezoneOffset);

        $chart  = new BarChart([
            $this->translator->trans('mailvotech.core.date.monday'),
            $this->translator->trans('mailvotech.core.date.tuesday'),
            $this->translator->trans('mailvotech.core.date.wednesday'),
            $this->translator->trans('mailvotech.core.date.thursday'),
            $this->translator->trans('mailvotech.core.date.friday'),
            $this->translator->trans('mailvotech.core.date.saturday'),
            $this->translator->trans('mailvotech.core.date.sunday'),
        ]);

        $chart->setDataset($this->translator->trans('mailvotech.email.sent'), array_column($stats, 'sent_count'));
        $chart->setDataset($this->translator->trans('mailvotech.email.read'), array_column($stats, 'read_count'));
        $chart->setDataset($this->translator->trans('mailvotech.email.click'), array_column($stats, 'hit_count'));

        return $this->render(
            '@MailVotechCore/Helper/chart.html.twig',
            [
                'chartData'   => $chart->render(),
                'chartType'   => 'bar',
                'chartHeight' => 300,
            ]
        );
    }

    public function emailHoursAction(
        EmailPeriodMetrics $emailPeriodMetrics,
        CampaignModel $model,
        int $objectId,
        string $dateFrom = '',
        string $dateTo = '',
    ): Response {
        $entity               = $model->getEntity($objectId);
        $eventsIds            = $entity->getEmailSendEvents()->getKeys();
        $dateFromObject       = new \DateTimeImmutable($dateFrom);
        $dateToObject         = new \DateTimeImmutable($dateTo);

        $dateTimeHelper        = new DateTimeHelper();
        $defaultTimezoneOffset = $dateTimeHelper->getLocalDateTime()->format('Z');

        $stats = $emailPeriodMetrics->emailMetricsPerHourByCampaignEvents($eventsIds, $dateFromObject, $dateToObject, $defaultTimezoneOffset);

        $hoursRange = range(0, 23);
        $labels     = [];

        $timeFormat = $this->coreParametersHelper->get('date_format_timeonly');

        foreach ($hoursRange as $hour) {
            $startTime = (new \DateTime())->setTime($hour, 0);
            $endTime   = (new \DateTime())->setTime(($hour + 1) % 24, 0);

            $labels[] = $startTime->format($timeFormat).' - '.$endTime->format($timeFormat);
        }

        $chart  = new BarChart($labels);
        $chart->setDataset($this->translator->trans('mailvotech.email.sent'), array_column($stats, 'sent_count'));
        $chart->setDataset($this->translator->trans('mailvotech.email.read'), array_column($stats, 'read_count'));
        $chart->setDataset($this->translator->trans('mailvotech.email.click'), array_column($stats, 'hit_count'));

        return $this->render(
            '@MailVotechCore/Helper/chart.html.twig',
            [
                'chartData'   => $chart->render(),
                'chartType'   => 'hour',
                'chartHeight' => 300,
            ]
        );
    }

    public function eventDetailsAction(
        EventDispatcherInterface $eventDispatcher,
        EventModel $eventModel,
        int $objectId,
    ): JsonResponse {
        $event    = $eventModel->getEntity($objectId);

        if (!$event) {
            return $this->json([
                'message' => $this->translator->trans('mailvotech.core.error.notfound', [], 'flashes'),
            ], Response::HTTP_NOT_FOUND);
        }

        $eventDetailsAction = new EventPreview($event);
        $eventDispatcher->dispatch($eventDetailsAction);

        return $this->json($eventDetailsAction->eventStats);
    }
}
