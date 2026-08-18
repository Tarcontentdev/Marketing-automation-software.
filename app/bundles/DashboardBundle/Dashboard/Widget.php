<?php

namespace MailVotech\DashboardBundle\Dashboard;

use MailVotech\CoreBundle\Helper\DateTimeHelper;
use MailVotech\CoreBundle\Helper\UserHelper;
use MailVotech\DashboardBundle\Model\DashboardModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class Widget
{
    public const FORMAT_HUMAN = 'M j, Y';

    public function __construct(
        private readonly DashboardModel $dashboardModel,
        private readonly UserHelper $userHelper,
        private readonly RequestStack $requestStack,
    ) {
    }

    /**
     * Get ready widget to populate in template.
     *
     * @return bool|\MailVotech\DashboardBundle\Entity\Widget
     */
    public function get(int $widgetId)
    {
        /** @var \MailVotech\DashboardBundle\Entity\Widget $widget */
        $widget = $this->dashboardModel->getEntity($widgetId);

        if (null === $widget || !$widget->getId()) {
            throw new NotFoundHttpException('Not found.');
        }

        if ($widget->getCreatedBy() !== $this->userHelper->getUser()->getId()) {
            // Unauthorized access
            throw new AccessDeniedException();
        }

        $filter = $this->dashboardModel->getDefaultFilter();

        $this->dashboardModel->populateWidgetContent($widget, $filter);

        return $widget;
    }

    /**
     * Set filter from POST to session.
     *
     * @throws \Exception
     */
    public function setFilter(Request $request): void
    {
        if (!$request->isMethod(Request::METHOD_POST)) {
            return;
        }

        $dateRangeFilter = $request->query->all()['daterange'] ?? $request->request->all()['daterange'] ?? [];

        if (!empty($dateRangeFilter['date_from'])) {
            $from = new \DateTime($dateRangeFilter['date_from']);
            $this->requestStack->getSession()->set('mailvotech.daterange.form.from', $from->format(DateTimeHelper::FORMAT_DB_DATE_ONLY));
        }

        if (!empty($dateRangeFilter['date_to'])) {
            $to = new \DateTime($dateRangeFilter['date_to']);
            $this->requestStack->getSession()->set('mailvotech.daterange.form.to', $to->format(DateTimeHelper::FORMAT_DB_DATE_ONLY));
        }

        $this->dashboardModel->clearDashboardCache();
    }
}
