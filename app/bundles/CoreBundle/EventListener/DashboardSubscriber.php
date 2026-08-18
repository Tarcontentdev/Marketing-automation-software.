<?php

namespace MailVotech\CoreBundle\EventListener;

use MailVotech\CoreBundle\Event\IconEvent;
use MailVotech\CoreBundle\Factory\ModelFactory;
use MailVotech\CoreBundle\Model\AuditLogModel;
use MailVotech\CoreBundle\Model\FormModel;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\DashboardBundle\Event\WidgetDetailEvent;
use MailVotech\DashboardBundle\EventListener\DashboardSubscriber as MainDashboardSubscriber;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class DashboardSubscriber extends MainDashboardSubscriber
{
    public const TYPE_RECENT_ACTIVITY = 'recent.activity';

    /**
     * Define the name of the bundle/category of the widget(s).
     *
     * @var string
     */
    protected $bundle = 'core';

    /**
     * Define the widget(s).
     *
     * @var array<string, array<string, string>>
     */
    protected $types = [
        self::TYPE_RECENT_ACTIVITY => [],
    ];

    /**
     * @param ModelFactory<object> $modelFactory
     */
    public function __construct(
        private readonly AuditLogModel $auditLogModel,
        private readonly TranslatorInterface $translator,
        private readonly RouterInterface $router,
        private readonly CorePermissions $security,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly ModelFactory $modelFactory,
    ) {
    }

    /**
     * Set a widget detail when needed.
     */
    public function onWidgetDetailGenerate(WidgetDetailEvent $event): void
    {
        if (self::TYPE_RECENT_ACTIVITY !== $event->getType()) {
            return;
        }

        if (!$event->isCached()) {
            $height = $event->getWidget()->getHeight();
            $limit  = (int) round(($height - 80) / 75);
            $logs   = $this->auditLogModel->getLogForObject(null, null, null, $limit);

            // Get names of log's items
            foreach ($logs as $key => &$log) {
                if (!isset($log['bundle'], $log['object'], $log['objectId'])) {
                    continue;
                }

                try {
                    $model = $this->modelFactory->getModel($log['bundle'].'.'.$log['object']);
                    $item  = $model->getEntity($log['objectId']);
                    if (null === $item) {
                        $log['objectName'] = $log['object'].'-'.$log['objectId'];
                    } elseif ($model instanceof FormModel && $model->getNameGetter() && method_exists($item, $model->getNameGetter())) {
                        $log['objectName'] = $item->{$model->getNameGetter()}();

                        if ('lead' === $log['bundle'] && 'mailvotech.lead.lead.anonymous' === $log['objectName']) {
                            $log['objectName'] = $this->translator->trans('mailvotech.lead.lead.anonymous');
                        }
                    } else {
                        $log['objectName'] = '';
                    }

                    $routeName = 'mailvotech_'.$log['bundle'].'_action';
                    if (null !== $item && null !== $this->router->getRouteCollection()->get($routeName)) {
                        $log['route'] = $this->router->generate(
                            $routeName,
                            ['objectAction' => 'view', 'objectId' => $log['objectId']]
                        );
                    } else {
                        $log['route'] = false;
                    }
                } catch (\Exception) {
                    unset($logs[$key]);
                }
            }
            unset($log);

            $iconEvent = new IconEvent($this->security);
            $this->dispatcher->dispatch($iconEvent);
            $event->setTemplateData(['logs' => $logs, 'icons' => $iconEvent->getIcons()]);
        }

        $event->setTemplate('@MailVotechDashboard/Dashboard/recentactivity.html.twig');
        $event->stopPropagation();
    }
}
