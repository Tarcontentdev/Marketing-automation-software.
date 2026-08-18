<?php

namespace MailVotechPlugin\MailVotechSocialBundle\Controller;

use MailVotech\CoreBundle\Controller\FormController;
use MailVotech\CoreBundle\Factory\PageHelperFactoryInterface;
use MailVotech\CoreBundle\Form\Type\DateRangeType;
use MailVotech\CoreBundle\Helper\Chart\LineChart;
use MailVotech\CoreBundle\Helper\IpLookupHelper;
use MailVotech\CoreBundle\Model\AuditLogModel;
use MailVotech\LeadBundle\Controller\EntityContactsTrait;
use MailVotechPlugin\MailVotechSocialBundle\Entity\Monitoring;
use MailVotechPlugin\MailVotechSocialBundle\Entity\PostCountRepository;
use MailVotechPlugin\MailVotechSocialBundle\Model\MonitoringModel;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\Required;

final class MonitoringController extends FormController
{
    use EntityContactsTrait;

    private PostCountRepository $postCountRepository;

    private AuditLogModel $auditLogModel;

    private MonitoringModel $monitoringModel;

    #[Required]
    public function autowireMonitoringController(
        MonitoringModel $monitoringModel,
        AuditLogModel $auditLogModel,
        PostCountRepository $postCountRepository,
    ): void {
        $this->monitoringModel = $monitoringModel;
        $this->auditLogModel = $auditLogModel;
        $this->postCountRepository = $postCountRepository;
    }

    /**
     * @param int $page
     */
    public function indexAction(Request $request, MonitoringModel $model, $page = 1): Response
    {
        if (!$this->security->isGranted('mailvotechSocial:monitoring:view')) {
            $this->throwAccessDenied();
        }

        $session = $request->getSession();

        $this->setListFilters();

        // set limits
        $limit = $session->get('mailvotech.social.monitoring.limit', $this->getParameter('mailvotech.default_pagelimit'));
        $start = (1 === $page) ? 0 : (($page - 1) * $limit);
        if ($start < 0) {
            $start = 0;
        }

        $search = $request->get('search', $session->get('mailvotech.social.monitoring.filter', ''));
        $session->set('mailvotech.social.monitoring.filter', $search);

        $filter = ['string' => $search, 'force' => []];

        $orderBy    = $session->get('mailvotech.social.monitoring.orderby', 'e.title');
        $orderByDir = $session->get('mailvotech.social.monitoring.orderbydir', 'DESC');

        $monitoringList = $model->getEntities(
            [
                'start'      => $start,
                'limit'      => $limit,
                'filter'     => $filter,
                'orderBy'    => $orderBy,
                'orderByDir' => $orderByDir,
            ]
        );

        $count = count($monitoringList);
        if ($count && $count < ($start + 1)) {
            // the number of entities are now less then the current asset so redirect to the last asset
            if (1 === $count) {
                $lastPage = 1;
            } else {
                $lastPage = (floor($limit / $count)) ?: 1;
            }
            $session->set('mailvotech.social.monitoring.page', $lastPage);
            $returnUrl = $this->generateUrl('mailvotech_social_index', ['page' => $lastPage]);

            return $this->postActionRedirect(
                [
                    'returnUrl'       => $returnUrl,
                    'viewParameters'  => ['page' => $lastPage],
                    'contentTemplate' => 'MailVotechPlugin\MailVotechSocialBundle\Controller\MonitoringController::indexAction',
                    'passthroughVars' => [
                        'activeLink'    => '#mailvotech_social_index',
                        'mailvotechContent' => 'monitoring',
                    ],
                ]
            );
        }

        // set what asset currently on so that we can return here after form submission/cancellation
        $session->set('mailvotech.social.monitoring.page', $page);

        $tmpl = $request->isXmlHttpRequest() ? $request->get('tmpl', 'index') : 'index';

        return $this->delegateView(
            [
                'viewParameters' => [
                    'searchValue' => $search,
                    'items'       => $monitoringList,
                    'limit'       => $limit,
                    'model'       => $model,
                    'tmpl'        => $tmpl,
                    'page'        => $page,
                ],
                'contentTemplate' => '@MailVotechSocial/Monitoring/list.html.twig',
                'passthroughVars' => [
                    'activeLink'    => '#mailvotech_social_index',
                    'mailvotechContent' => 'monitoring',
                    'route'         => $this->generateUrl('mailvotech_social_index', ['page' => $page]),
                ],
            ]
        );
    }

    /**
     * Generates new form and processes post data.
     */
    public function newAction(Request $request, MonitoringModel $model, IpLookupHelper $ipLookupHelper): Response
    {
        if (!$this->security->isGranted('mailvotechSocial:monitoring:create')) {
            $this->throwAccessDenied();
        }

        $action = $this->generateUrl('mailvotech_social_action', ['objectAction' => 'new']);

        $entity  = $model->getEntity();
        $method  = $request->getMethod();
        $session = $request->getSession();

        // get the list of types from the model
        $networkTypes = $model->getNetworkTypes();

        // get the network type from the request on submit. helpful for validation error
        // rebuilds structure of the form when it gets updated on submit
        $monitoring  = $request->request->all()['monitoring'] ?? [];
        $networkType = 'POST' === $method ? ($monitoring['networkType'] ?? '') : '';

        // build the form
        $form = $model->createForm(
            $entity,
            $this->formFactory,
            $action,
            [
                // pass through the types and the selected default type
                'networkTypes' => $networkTypes,
                'networkType'  => $networkType,
            ]
        );

        // Set the page we came from
        $page = $session->get('mailvotech.social.monitoring.page', 1);
        // /Check for a submitted form and process it
        if ('POST' === $method) {
            $viewParameters = ['page' => $page];
            $template       = 'MailVotechPlugin\MailVotechSocialBundle\Controller\MonitoringController::indexAction';
            $valid          = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    // form is valid so process the data
                    $model->saveEntity($entity);

                    // update the audit log
                    $this->updateAuditLog($entity, $ipLookupHelper, 'create');

                    $this->addFlashMessage(
                        'mailvotech.core.notice.created',
                        [
                            '%name%'      => $entity->getTitle(),
                            '%menu_link%' => 'mailvotech_social_index',
                            '%url%'       => $this->generateUrl(
                                'mailvotech_social_action',
                                [
                                    'objectAction' => 'edit',
                                    'objectId'     => $entity->getId(),
                                ]
                            ),
                        ]
                    );

                    if (!$this->getFormButton($form, ['buttons', 'save'])->isClicked()) {
                        // return edit view so that all the session stuff is loaded
                        return $this->editAction($request, $ipLookupHelper, $entity->getId(), true);
                    }

                    $viewParameters = [
                        'objectAction' => 'view',
                        'objectId'     => $entity->getId(),
                    ];
                    $template = 'MailVotechPlugin\MailVotechSocialBundle\Controller\MonitoringController::viewAction';
                }
            }
            $returnUrl = $this->generateUrl('mailvotech_social_index', $viewParameters);

            /** @var SubmitButton $saveSubmitButton */
            $saveSubmitButton = $form->get('buttons')->get('save');

            if ($cancelled || ($valid && $saveSubmitButton->isClicked())) {
                return $this->postActionRedirect(
                    [
                        'returnUrl'       => $returnUrl,
                        'viewParameters'  => $viewParameters,
                        'contentTemplate' => $template,
                        'passthroughVars' => [
                            'activeLink'    => 'mailvotech_social_index',
                            'mailvotechContent' => 'monitoring',
                        ],
                    ]
                );
            }
        }

        return $this->delegateView(
            [
                'viewParameters' => [
                    'tmpl'   => $request->isXmlHttpRequest() ? $request->get('tmpl', 'index') : 'index',
                    'entity' => $entity,
                    'form'   => $form->createView(),
                ],
                'contentTemplate' => '@MailVotechSocial/Monitoring/form.html.twig',
                'passthroughVars' => [
                    'activeLink'    => '#mailvotech_social_index',
                    'mailvotechContent' => 'monitoring',
                    'route'         => $this->generateUrl(
                        'mailvotech_social_action',
                        [
                            'objectAction' => 'new',
                            'objectId'     => $entity->getId(),
                        ]
                    ),
                ],
            ]
        );
    }

    public function editAction(Request $request, IpLookupHelper $ipLookupHelper, $objectId, bool $ignorePost = false): Response
    {
        if (!$this->security->isGranted('mailvotechSocial:monitoring:edit')) {
            $this->throwAccessDenied();
        }

        $action = $this->generateUrl('mailvotech_social_action', ['objectAction' => 'edit', 'objectId' => $objectId]);

        $entity  = $this->monitoringModel->getEntity($objectId);
        $session = $request->getSession();

        // Set the page we came from
        $page = $session->get('mailvotech.social.monitoring.page', 1);

        // set the return URL
        $returnUrl = $this->generateUrl('mailvotech_social_index', ['page' => $page]);

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MailVotechSocial:Monitoring:index',
            'passthroughVars' => [
                'activeLink'    => 'mailvotech_social_index',
                'mailvotechContent' => 'monitoring',
            ],
        ];

        // not found
        if (null === $entity) {
            return $this->postActionRedirect(
                array_merge(
                    $postActionVars,
                    [
                        'flashes' => [
                            [
                                'type'    => 'error',
                                'msg'     => 'mailvotech.social.monitoring.error.notfound',
                                'msgVars' => ['%id%' => $objectId],
                            ],
                        ],
                    ]
                )
            );
        }

        // get the list of types from the model
        $networkTypes = $this->monitoringModel->getNetworkTypes();

        // get the network type from the request on submit. helpful for validation error
        // rebuilds structure of the form when it gets updated on submit
        $method      = $request->getMethod();
        $monitoring  = $request->request->all()['monitoring'] ?? [];
        $networkType = 'POST' === $method ? ($monitoring['networkType'] ?? '') : $entity->getNetworkType();

        // build the form
        $form = $this->monitoringModel->createForm(
            $entity,
            $this->formFactory,
            $action,
            [
                // pass through the types and the selected default type
                'networkTypes' => $networkTypes,
                'networkType'  => $networkType,
            ]
        );

        // /Check for a submitted form and process it
        if (!$ignorePost && 'POST' === $method) {
            $valid = false;

            /** @var SubmitButton $saveSubmitButton */
            $saveSubmitButton = $form->get('buttons')->get('save');

            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    // form is valid so process the data
                    $this->monitoringModel->saveEntity($entity, $saveSubmitButton->isClicked());

                    // update the audit log
                    $this->updateAuditLog($entity, $ipLookupHelper, 'update');

                    $this->addFlashMessage(
                        'mailvotech.core.notice.updated',
                        [
                            '%name%'      => $entity->getTitle(),
                            '%menu_link%' => 'mailvotech_email_index',
                            '%url%'       => $this->generateUrl(
                                'mailvotech_social_action',
                                [
                                    'objectAction' => 'edit',
                                    'objectId'     => $entity->getId(),
                                ]
                            ),
                        ],
                        'warning'
                    );
                }
            } else {
                $this->monitoringModel->unlockEntity($entity);
            }

            if ($cancelled || ($valid && $saveSubmitButton->isClicked())) {
                $viewParameters = [
                    'objectAction' => 'view',
                    'objectId'     => $entity->getId(),
                ];

                return $this->postActionRedirect(
                    array_merge(
                        $postActionVars,
                        [
                            'returnUrl'       => $this->generateUrl('mailvotech_social_action', $viewParameters),
                            'viewParameters'  => $viewParameters,
                            'contentTemplate' => 'MailVotechPlugin\MailVotechSocialBundle\Controller\MonitoringController::viewAction',
                        ]
                    )
                );
            }
        } else {
            // lock the entity
            $this->monitoringModel->lockEntity($entity);
        }

        return $this->delegateView(
            [
                'viewParameters' => [
                    'tmpl'   => $request->isXmlHttpRequest() ? $request->get('tmpl', 'index') : 'index',
                    'entity' => $entity,
                    'form'   => $form->createView(),
                ],
                'contentTemplate' => '@MailVotechSocial/Monitoring/form.html.twig',
                'passthroughVars' => [
                    'activeLink'    => '#mailvotech_social_index',
                    'mailvotechContent' => 'monitoring',
                    'route'         => $this->generateUrl(
                        'mailvotech_social_action',
                        [
                            'objectAction' => 'edit',
                            'objectId'     => $entity->getId(),
                        ]
                    ),
                ],
            ]
        );
    }

    /**
     * Loads a specific form into the detailed panel.
     *
     * @param int $objectId
     */
    public function viewAction(Request $request, $objectId): Response
    {
        if (!$this->security->isGranted('mailvotechSocial:monitoring:view')) {
            $this->throwAccessDenied();
        }

        $session = $request->getSession();

        $security         = $this->security;
        $monitoringEntity = $this->monitoringModel->getEntity($objectId);

        // set the asset we came from
        $page = $session->get('mailvotech.social.monitoring.page', 1);

        $tmpl = $request->isXmlHttpRequest() ? $request->get('tmpl', 'details') : 'details';

        if (null === $monitoringEntity) {
            // set the return URL
            $returnUrl = $this->generateUrl('mailvotech_social_index', ['page' => $page]);

            return $this->postActionRedirect(
                [
                    'returnUrl'       => $returnUrl,
                    'viewParameters'  => ['page' => $page],
                    'contentTemplate' => 'MailVotechPlugin\MailVotechSocialBundle\Controller\MonitoringController::indexAction',
                    'passthroughVars' => [
                        'activeLink'    => '#mailvotech_social_index',
                        'mailvotechContent' => 'monitoring',
                    ],
                    'flashes' => [
                        [
                            'type'    => 'error',
                            'msg'     => 'mailvotech.social.monitoring.error.notfound',
                            'msgVars' => ['%id%' => $objectId],
                        ],
                    ],
                ]
            );
        }
        $logs = $this->auditLogModel->getLogForObject('monitoring', $objectId);

        $returnUrl = $this->generateUrl(
            'mailvotech_social_action',
            [
                'objectAction' => 'view',
                'objectId'     => $monitoringEntity->getId(),
            ]
        );

        // Init the date range filter form
        $dateRangeValues = $request->get('daterange', []);
        $dateRangeForm   = $this->formFactory->create(DateRangeType::class, $dateRangeValues, ['action' => $returnUrl]);
        $dateFrom        = new \DateTime($dateRangeForm['date_from']->getData());
        $dateTo          = new \DateTime($dateRangeForm['date_to']->getData());

        $chart     = new LineChart(null, $dateFrom, $dateTo);
        $leadStats = $this->postCountRepository->getLeadStatsPost(
            $dateFrom,
            $dateTo,
            ['monitor_id' => $monitoringEntity->getId()]
        );
        $chart->setDataset($this->translator->trans('mailvotech.social.twitter.tweet.count'), $leadStats);

        return $this->delegateView(
            [
                'returnUrl'      => $returnUrl,
                'viewParameters' => [
                    'activeMonitoring' => $monitoringEntity,
                    'logs'             => $logs,
                    'isEmbedded'       => $request->get('isEmbedded') ?: false,
                    'tmpl'             => $tmpl,
                    'security'         => $security,
                    'leadStats'        => $chart->render(),
                    'monitorLeads'     => $this->forward(
                        'MailVotechPlugin\MailVotechSocialBundle\Controller\MonitoringController::contactsAction',
                        [
                            'objectId'   => $monitoringEntity->getId(),
                            'page'       => $page,
                            'ignoreAjax' => true,
                        ]
                    )->getContent(),
                    'dateRangeForm' => $dateRangeForm->createView(),
                ],
                'contentTemplate' => '@MailVotechSocial/Monitoring/'.$tmpl.'.html.twig',
                'passthroughVars' => [
                    'activeLink'    => '#mailvotech_social_index',
                    'mailvotechContent' => 'monitoring',
                ],
            ]
        );
    }

    /**
     * Deletes the entity.
     *
     * @param int $objectId
     */
    public function deleteAction(Request $request, IpLookupHelper $ipLookupHelper, $objectId): Response
    {
        if (!$this->security->isGranted('mailvotechSocial:monitoring:delete')) {
            $this->throwAccessDenied();
        }

        $session   = $request->getSession();
        $page      = $session->get('mailvotech.social.monitoring.page', 1);
        $returnUrl = $this->generateUrl('mailvotech_social_index', ['page' => $page]);
        $flashes   = [];

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MailVotechPlugin\MailVotechSocialBundle\Controller\MonitoringController::indexAction',
            'passthroughVars' => [
                'activeLink'    => 'mailvotech_social_index',
                'mailvotechContent' => 'monitoring',
            ],
        ];

        if ('POST' === $request->getMethod()) {
            $entity = $this->monitoringModel->getEntity($objectId);

            if (null === $entity) {
                $flashes[] = [
                    'type'    => 'error',
                    'msg'     => 'mailvotech.social.monitoring.error.notfound',
                    'msgVars' => ['%id%' => $objectId],
                ];
            } elseif ($this->monitoringModel->isLocked($entity)) {
                return $this->isLocked($postActionVars, $entity, 'plugin.mailvotechSocial.monitoring');
            }

            // update the audit log
            $this->updateAuditLog($entity, $ipLookupHelper, 'delete');

            // then delete the record
            $this->monitoringModel->deleteEntity($entity);

            $flashes[] = [
                'type'    => 'notice',
                'msg'     => 'mailvotech.core.notice.deleted',
                'msgVars' => [
                    '%name%' => $entity->getTitle(),
                    '%id%'   => $objectId,
                ],
            ];
        } // else don't do anything

        return $this->postActionRedirect(
            array_merge(
                $postActionVars,
                [
                    'flashes' => $flashes,
                ]
            )
        );
    }

    /**
     * Deletes a group of entities.
     */
    public function batchDeleteAction(Request $request): Response
    {
        if (!$this->security->isGranted('mailvotechSocial:monitoring:delete')) {
            $this->throwAccessDenied();
        }

        $session   = $request->getSession();
        $page      = $session->get('mailvotech.social.monitoring.page', 1);
        $returnUrl = $this->generateUrl('mailvotech_social_index', ['page' => $page]);
        $flashes   = [];

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MailVotechPlugin\MailVotechSocialBundle\Controller\MonitoringController::indexAction',
            'passthroughVars' => [
                'activeLink'    => '#mailvotech_social_index',
                'mailvotechContent' => 'monitoring',
            ],
        ];

        if ('POST' === $request->getMethod()) {
            $ids       = json_decode($request->query->get('ids', ''));
            $deleteIds = [];

            // Loop over the IDs to perform access checks pre-delete
            foreach ($ids as $objectId) {
                $entity = $this->monitoringModel->getEntity($objectId);

                if (null === $entity) {
                    $flashes[] = [
                        'type'    => 'error',
                        'msg'     => 'mailvotech.social.monitoring.error.notfound',
                        'msgVars' => ['%id%' => $objectId],
                    ];
                } elseif ($this->monitoringModel->isLocked($entity)) {
                    $flashes[] = $this->isLocked($postActionVars, $entity, 'monitoring', true);
                } else {
                    $deleteIds[] = $objectId;
                }
            }

            // Delete everything we are able to
            if ([] !== $deleteIds) {
                $entities = $this->monitoringModel->deleteEntities($deleteIds);

                $flashes[] = [
                    'type'    => 'notice',
                    'msg'     => 'mailvotech.social.monitoring.notice.batch_deleted',
                    'msgVars' => [
                        '%count%' => count($entities),
                    ],
                ];
            }
        } // else don't do anything

        return $this->postActionRedirect(
            array_merge(
                $postActionVars,
                [
                    'flashes' => $flashes,
                ]
            )
        );
    }

    /**
     * @param int $page
     */
    public function contactsAction(
        Request $request,
        PageHelperFactoryInterface $pageHelperFactory,
        $objectId,
        $page = 1,
    ): Response {
        return $this->generateContactsGrid(
            $request,
            $pageHelperFactory,
            $objectId,
            $page,
            'mailvotechSocial:monitoring:view',
            'social',
            'monitoring_leads',
            null, // @todo - implement when individual social channels are supported by the plugin
            'monitor_id'
        );
    }

    public function updateAuditLog(Monitoring $monitoring, IpLookupHelper $ipLookupHelper, $action): void
    {
        $log = [
            'bundle'    => 'plugin.mailvotechSocial',
            'object'    => 'monitoring',
            'objectId'  => $monitoring->getId(),
            'action'    => $action,
            'details'   => ['name' => $monitoring->getTitle()],
            'ipAddress' => $ipLookupHelper->getIpAddressFromRequest(),
        ];
        $this->auditLogModel->writeToLog($log);
    }
}
