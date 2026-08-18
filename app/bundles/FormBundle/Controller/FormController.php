<?php

namespace MailVotech\FormBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use MailVotech\CoreBundle\Controller\FormController as CommonFormController;
use MailVotech\CoreBundle\Factory\ModelFactory;
use MailVotech\CoreBundle\Factory\PageHelperFactoryInterface;
use MailVotech\CoreBundle\Form\Type\DateRangeType;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Helper\ThemeHelper;
use MailVotech\CoreBundle\Helper\UserHelper;
use MailVotech\CoreBundle\Model\AuditLogModel;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\CoreBundle\Service\FlashBag;
use MailVotech\CoreBundle\Translation\Translator;
use MailVotech\CoreBundle\Twig\Helper\AnalyticsHelper;
use MailVotech\CoreBundle\Twig\Helper\AssetsHelper;
use MailVotech\FormBundle\Collector\AlreadyMappedFieldCollectorInterface;
use MailVotech\FormBundle\Collector\MappedObjectCollector;
use MailVotech\FormBundle\Entity\Field;
use MailVotech\FormBundle\Entity\Form;
use MailVotech\FormBundle\Entity\FormRepository;
use MailVotech\FormBundle\Entity\SubmissionRepository;
use MailVotech\FormBundle\Exception\ValidationException;
use MailVotech\FormBundle\Helper\FormFieldHelper;
use MailVotech\FormBundle\Model\FormModel;
use MailVotech\FormBundle\Model\SubmissionModel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class FormController extends CommonFormController
{
    public function __construct(
        FormFactoryInterface $formFactory,
        FormFieldHelper $fieldHelper,
        private readonly AlreadyMappedFieldCollectorInterface $alreadyMappedFieldCollector,
        private readonly MappedObjectCollector $mappedObjectCollector,
        ManagerRegistry $doctrine,
        ModelFactory $modelFactory,
        UserHelper $userHelper,
        CoreParametersHelper $coreParametersHelper,
        EventDispatcherInterface $dispatcher,
        Translator $translator,
        FlashBag $flashBag,
        RequestStack $requestStack,
        CorePermissions $security,
        private readonly FormModel $formModel,
        private readonly AuditLogModel $auditLogModel,
        private readonly SubmissionModel $submissionModel,
        private readonly SubmissionRepository $submissionRepository,
        private readonly FormRepository $formRepository,
    ) {
        parent::__construct($formFactory, $fieldHelper, $doctrine, $modelFactory, $userHelper, $coreParametersHelper, $dispatcher, $translator, $flashBag, $requestStack, $security);
    }

    public function indexAction(Request $request, PageHelperFactoryInterface $pageHelperFactory, int $page = 1): Response
    {
        // set some permissions
        $permissions = $this->security->isGranted(
            [
                'form:forms:viewown',
                'form:forms:viewother',
                'form:forms:create',
                'form:forms:editown',
                'form:forms:editother',
                'form:forms:deleteown',
                'form:forms:deleteother',
                'form:forms:publishown',
                'form:forms:publishother',
            ],
            'RETURN_ARRAY'
        );

        if (!$permissions['form:forms:viewown'] && !$permissions['form:forms:viewother']) {
            $this->throwAccessDenied();
        }

        $this->setListFilters();

        $session = $request->getSession();

        $pageHelper = $pageHelperFactory->make('mailvotech.form', $page);
        $limit      = $pageHelper->getLimit();
        $start      = $pageHelper->getStart();
        $search     = $request->get('search', $session->get('mailvotech.form.filter', ''));
        $filter     = ['string' => $search, 'force' => []];
        $session->set('mailvotech.form.filter', $search);

        if (!$permissions['form:forms:viewother']) {
            $filter['force'][] = ['column' => 'f.createdBy', 'expr' => 'eq', 'value' => $this->user->getId()];
        }

        $orderBy    = $session->get('mailvotech.form.orderby', 'f.dateModified');
        $orderByDir = $session->get('mailvotech.form.orderbydir', $this->getDefaultOrderDirection());
        $forms      = $this->formModel->getEntities(
            [
                'start'      => $start,
                'limit'      => $limit,
                'filter'     => $filter,
                'orderBy'    => $orderBy,
                'orderByDir' => $orderByDir,
            ]
        );

        $count = count($forms);

        if ($count && $count < ($start + 1)) {
            // the number of entities are now less then the current page so redirect to the last page
            $lastPage = $pageHelper->countPage($count);
            $pageHelper->rememberPage($lastPage);
            $returnUrl = $this->generateUrl('mailvotech_form_index', ['page' => $lastPage]);

            return $this->postActionRedirect(
                [
                    'returnUrl'       => $returnUrl,
                    'viewParameters'  => ['page' => $lastPage],
                    'contentTemplate' => 'MailVotech\FormBundle\Controller\FormController::indexAction',
                    'passthroughVars' => [
                        'activeLink'    => '#mailvotech_form_index',
                        'mailvotechContent' => 'form',
                    ],
                ]
            );
        }

        $pageHelper->rememberPage($page);

        return $this->delegateView(
            [
                'viewParameters'  => [
                    'searchValue' => $search,
                    'items'       => $forms,
                    'totalItems'  => $count,
                    'page'        => $page,
                    'limit'       => $limit,
                    'permissions' => $permissions,
                    'security'    => $this->security,
                    'tmpl'        => $request->get('tmpl', 'index'),
                ],
                'contentTemplate' => '@MailVotechForm/Form/list.html.twig',
                'passthroughVars' => [
                    'activeLink'    => '#mailvotech_form_index',
                    'mailvotechContent' => 'form',
                    'route'         => $this->generateUrl('mailvotech_form_index', ['page' => $page]),
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
        $activeForm = $this->formModel->getEntity($objectId);

        // set the page we came from
        $page = $request->getSession()->get('mailvotech.form.page', 1);

        if (null === $activeForm) {
            // set the return URL
            $returnUrl = $this->generateUrl('mailvotech_form_index', ['page' => $page]);

            return $this->postActionRedirect(
                [
                    'returnUrl'       => $returnUrl,
                    'viewParameters'  => ['page' => $page],
                    'contentTemplate' => 'MailVotech\FormBundle\Controller\FormController::indexAction',
                    'passthroughVars' => [
                        'activeLink'    => '#mailvotech_form_index',
                        'mailvotechContent' => 'form',
                    ],
                    'flashes' => [
                        [
                            'type'    => 'error',
                            'msg'     => 'mailvotech.form.error.notfound',
                            'msgVars' => ['%id%' => $objectId],
                        ],
                    ],
                ]
            );
        }
        if (!$this->security->hasEntityAccess(
            'form:forms:viewown',
            'form:forms:viewother',
            $activeForm->getCreatedBy()
        )
        ) {
            $this->throwAccessDenied();
        }

        $permissions = $this->security->isGranted(
            [
                'form:forms:viewown',
                'form:forms:viewother',
                'form:forms:create',
                'form:forms:editown',
                'form:forms:editother',
                'form:forms:deleteown',
                'form:forms:deleteother',
                'form:forms:publishown',
                'form:forms:publishother',
            ],
            'RETURN_ARRAY'
        );
        $logs = $this->auditLogModel->getLogForObject('form', $objectId, $activeForm->getDateAdded());

        // Init the date range filter form
        $dateRangeValues = $request->query->all()['daterange'] ?? $request->request->all()['daterange'] ?? [];
        $action          = $this->generateUrl('mailvotech_form_action', ['objectAction' => 'view', 'objectId' => $objectId]);
        $dateRangeForm   = $this->formFactory->create(DateRangeType::class, $dateRangeValues, ['action' => $action]);
        // Submission stats per time period
        $timeStats = $this->submissionModel->getSubmissionsLineChartData(
            null,
            new \DateTime($dateRangeForm->get('date_from')->getData()),
            new \DateTime($dateRangeForm->get('date_to')->getData()),
            null,
            ['form_id' => $objectId]
        );

        // Only show actions and fields that still exist
        $customComponents  = $this->formModel->getCustomComponents();
        $activeFormActions = [];
        foreach ($activeForm->getActions() as $formAction) {
            if (!isset($customComponents['actions'][$formAction->getType()])) {
                continue;
            }
            $type                          = explode('.', $formAction->getType());
            $activeFormActions[$type[0]][] = $formAction;
        }

        $activeFormFields = [];
        $availableFields  = array_flip($this->fieldHelper->getChoiceList($customComponents['fields']));
        foreach ($activeForm->getFields() as $field) {
            if (!isset($availableFields[$field->getType()])) {
                continue;
            }

            $activeFormFields[] = $field;
        }

        $submissionCounts = $this->submissionRepository->getSubmissionCounts($activeForm);

        return $this->delegateView(
            [
                'viewParameters' => [
                    'activeForm'       => $activeForm,
                    'submissionCounts' => $submissionCounts,
                    'page'             => $page,
                    'logs'             => $logs,
                    'permissions'      => $permissions,
                    'stats'            => [
                        'submissionsInTime' => $timeStats,
                    ],
                    'dateRangeForm'     => $dateRangeForm->createView(),
                    'activeFormActions' => $activeFormActions,
                    'activeFormFields'  => $activeFormFields,
                    'formScript'        => htmlspecialchars($this->formModel->getFormScript($activeForm), ENT_QUOTES, 'UTF-8'),
                    'formContent'       => htmlspecialchars($this->formModel->getContent($activeForm, false), ENT_QUOTES, 'UTF-8'),
                    'availableActions'  => $customComponents['actions'],
                ],
                'contentTemplate' => '@MailVotechForm/Form/details.html.twig',
                'passthroughVars' => [
                    'activeLink'    => '#mailvotech_form_index',
                    'mailvotechContent' => 'form',
                    'route'         => $action,
                ],
            ]
        );
    }

    /**
     * Generates new form and processes post data.
     *
     * @throws \Exception
     */
    public function newAction(Request $request): Response
    {
        $entity  = $this->formModel->getEntity();
        $session = $request->getSession();

        if (!$this->security->isGranted('form:forms:create')) {
            $this->throwAccessDenied();
        }

        // set the page we came from
        $page       = $request->getSession()->get('mailvotech.form.page', 1);
        $mailvotechform = $request->request->all()['mailvotechform'] ?? [];
        $sessionId  = $mailvotechform['sessionId'] ?? 'mailvotech_'.sha1(uniqid(mt_rand(), true));

        // set added/updated fields
        $modifiedFields = $session->get('mailvotech.form.'.$sessionId.'.fields.modified', []);
        $deletedFields  = $session->get('mailvotech.form.'.$sessionId.'.fields.deleted', []);

        // set added/updated actions
        $modifiedActions = $session->get('mailvotech.form.'.$sessionId.'.actions.modified', []);
        $deletedActions  = $session->get('mailvotech.form.'.$sessionId.'.actions.deleted', []);

        $action = $this->generateUrl('mailvotech_form_action', ['objectAction' => 'new']);
        $form   = $this->formModel->createForm($entity, $this->formFactory, $action);

        // /Check for a submitted form and process it
        if ('POST' === $request->getMethod()) {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    // only save fields that are not to be deleted
                    $fields = array_diff_key($modifiedFields, array_flip($deletedFields));

                    // make sure that at least one field is selected
                    if ([] === $fields) {
                        // set the error
                        $form->addError(
                            new FormError(
                                $this->translator->trans('mailvotech.form.form.fields.notempty', [], 'validators')
                            )
                        );
                        $valid = false;
                    } else {
                        $this->formModel->setFields($entity, $fields);

                        try {
                            // Set alias to prevent SQL errors
                            $alias = $this->formModel->cleanAlias($entity->getName(), '', 10);
                            $entity->setAlias($alias);

                            // Set timestamps
                            $this->formModel->setTimestamps($entity, true, false);

                            // Save the form first and new actions so that new fields are available to actions.
                            // Using the repository function to not trigger the listeners twice.

                            $this->formRepository->saveEntity($entity);

                            // Only save actions that are not to be deleted
                            $actions = array_diff_key($modifiedActions, array_flip($deletedActions));

                            // Set and persist actions
                            $this->formModel->setActions($entity, $actions);

                            // Save and trigger listeners
                            $this->formModel->saveEntity($entity, $this->getFormButton($form, ['buttons', 'save'])->isClicked());

                            $this->addFlashMessage(
                                'mailvotech.core.notice.created',
                                [
                                    '%name%'      => $entity->getName(),
                                    '%menu_link%' => 'mailvotech_form_index',
                                    '%url%'       => $this->generateUrl(
                                        'mailvotech_form_action',
                                        [
                                            'objectAction' => 'edit',
                                            'objectId'     => $entity->getId(),
                                        ]
                                    ),
                                ]
                            );

                            if ($this->getFormButton($form, ['buttons', 'save'])->isClicked()) {
                                $viewParameters = [
                                    'objectAction' => 'view',
                                    'objectId'     => $entity->getId(),
                                ];
                                $returnUrl = $this->generateUrl('mailvotech_form_action', $viewParameters);
                                $template  = 'MailVotech\FormBundle\Controller\FormController::viewAction';
                            } else {
                                // return edit view so that all the session stuff is loaded
                                return $this->editAction($request, $entity->getId(), true);
                            }
                        } catch (ValidationException $ex) {
                            $form->addError(
                                new FormError(
                                    $ex->getMessage()
                                )
                            );
                            $valid = false;
                        } catch (\Exception $e) {
                            $form['name']->addError(
                                new FormError($this->translator->trans('mailvotech.form.schema.failed', [], 'validators'))
                            );
                            $valid = false;

                            if ('dev' == $this->getParameter('kernel.environment')) {
                                throw $e;
                            }
                        }
                    }
                }
            } else {
                $viewParameters = ['page' => $page];
                $returnUrl      = $this->generateUrl('mailvotech_form_index', $viewParameters);
                $template       = 'MailVotech\FormBundle\Controller\FormController::indexAction';
            }

            if ($cancelled || ($valid && $this->getFormButton($form, ['buttons', 'save'])->isClicked())) {
                // clear temporary fields
                $this->clearSessionComponents($request, $sessionId);

                return $this->postActionRedirect(
                    [
                        'returnUrl'       => $returnUrl,
                        'viewParameters'  => $viewParameters,
                        'contentTemplate' => $template,
                        'passthroughVars' => [
                            'activeLink'    => '#mailvotech_form_index',
                            'mailvotechContent' => 'form',
                        ],
                    ]
                );
            }
        } else {
            // clear out existing fields in case the form was refreshed, browser closed, etc
            $this->clearSessionComponents($request, $sessionId);
            $modifiedFields = $modifiedActions = $deletedActions = $deletedFields = [];

            $form->get('sessionId')->setData($sessionId);

            // add a submit button
            $keyId = 'new'.hash('sha1', uniqid(mt_rand()));
            $field = new Field();

            $modifiedFields[$keyId]                    = $field->convertToArray();
            $modifiedFields[$keyId]['label']           = $this->translator->trans('mailvotech.core.form.submit');
            $modifiedFields[$keyId]['alias']           = 'submit';
            $modifiedFields[$keyId]['showLabel']       = 1;
            $modifiedFields[$keyId]['type']            = 'button';
            $modifiedFields[$keyId]['id']              = $keyId;
            $modifiedFields[$keyId]['inputAttributes'] = 'class="btn btn-ghost"';
            $modifiedFields[$keyId]['formId']          = $sessionId;
            unset($modifiedFields[$keyId]['form']);
            $session->set('mailvotech.form.'.$sessionId.'.fields.modified', $modifiedFields);
        }

        // fire the form builder event
        $customComponents = $this->formModel->getCustomComponents();

        return $this->delegateView(
            [
                'viewParameters' => [
                    'fields'         => $this->fieldHelper->getChoiceList($customComponents['fields']),
                    'formFields'     => $modifiedFields,
                    'mappedFields'   => $this->mappedObjectCollector->buildCollection(...$entity->getMappedFieldObjects()),
                    'deletedFields'  => $deletedFields,
                    'viewOnlyFields' => $customComponents['viewOnlyFields'],
                    'actions'        => $customComponents['choices'],
                    'actionSettings' => $customComponents['actions'],
                    'formActions'    => $modifiedActions,
                    'deletedActions' => $deletedActions,
                    'tmpl'           => $request->isXmlHttpRequest() ? $request->get('tmpl', 'index') : 'index',
                    'activeForm'     => $entity,
                    'form'           => $form->createView(),
                    'inBuilder'      => true,
                ],
                'contentTemplate' => '@MailVotechForm/Builder/index.html.twig',
                'passthroughVars' => [
                    'activeLink'    => '#mailvotech_form_index',
                    'mailvotechContent' => 'form',
                    'route'         => $this->generateUrl(
                        'mailvotech_form_action',
                        [
                            'objectAction' => (!empty($valid) ? 'edit' : 'new'), // valid means a new form was applied
                            'objectId'     => $entity->getId(),
                        ]
                    ),
                ],
            ]
        );
    }

    /**
     * Generates edit form and processes post data.
     *
     * @param int|Form $objectId
     * @param bool     $ignorePost
     * @param bool     $forceTypeSelection
     */
    public function editAction(Request $request, $objectId, $ignorePost = false, $forceTypeSelection = false): Response
    {
        $formData         = $request->request->all()['mailvotechform'] ?? [];
        $sessionId        = $formData['sessionId'] ?? null;
        $customComponents = $this->formModel->getCustomComponents();
        $modifiedFields   = [];
        $deletedFields    = [];
        $modifiedActions  = [];
        $deletedActions   = [];

        if ($objectId instanceof Form) {
            $entity   = $objectId;
            $objectId = 'mailvotech_'.sha1(uniqid(mt_rand(), true));
        } else {
            $entity = $this->formModel->getEntity($objectId);

            // Process submit of cloned form
            if (null == $entity && $objectId == $sessionId) {
                $entity = $this->formModel->getEntity();
            }
        }

        $session    = $request->getSession();

        // set the page we came from
        $page = $request->getSession()->get('mailvotech.form.page', 1);

        // set the return URL
        $returnUrl = $this->generateUrl('mailvotech_form_index', ['page' => $page]);

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MailVotech\FormBundle\Controller\FormController::indexAction',
            'passthroughVars' => [
                'activeLink'    => '#mailvotech_form_index',
                'mailvotechContent' => 'form',
            ],
        ];

        // form not found
        if (null === $entity) {
            return $this->postActionRedirect(
                array_merge(
                    $postActionVars,
                    [
                        'flashes' => [
                            [
                                'type'    => 'error',
                                'msg'     => 'mailvotech.form.error.notfound',
                                'msgVars' => ['%id%' => $objectId],
                            ],
                        ],
                    ]
                )
            );
        }
        if (!$this->security->hasEntityAccess(
            'form:forms:editown',
            'form:forms:editother',
            $entity->getCreatedBy()
        )
        ) {
            $this->throwAccessDenied();
        } elseif ($this->formModel->isLocked($entity)) {
            // deny access if the entity is locked
            return $this->isLocked($postActionVars, $entity, 'form.form');
        }

        $action = $this->generateUrl('mailvotech_form_action', ['objectAction' => 'edit', 'objectId' => $objectId]);
        $form   = $this->formModel->createForm($entity, $this->formFactory, $action);

        // /Check for a submitted form and process it
        if (!$ignorePost && 'POST' === $request->getMethod()) {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                // set added/updated fields
                $modifiedFields = $session->get('mailvotech.form.'.$objectId.'.fields.modified', []);
                $deletedFields  = $session->get('mailvotech.form.'.$objectId.'.fields.deleted', []);
                $fields         = array_diff_key($modifiedFields, array_flip($deletedFields));

                // set added/updated actions
                $modifiedActions = $session->get('mailvotech.form.'.$objectId.'.actions.modified', []);
                $deletedActions  = $session->get('mailvotech.form.'.$objectId.'.actions.deleted', []);
                $actions         = array_diff_key($modifiedActions, array_flip($deletedActions));

                if ($valid = $this->isFormValid($form)) {
                    // make sure that at least one field is selected
                    if ([] === $fields) {
                        // set the error
                        $form->addError(
                            new FormError(
                                $this->translator->trans('mailvotech.form.form.fields.notempty', [], 'validators')
                            )
                        );
                        $valid = false;
                    } else {
                        $this->formModel->setFields($entity, $fields);
                        $this->formModel->deleteFields($entity, $deletedFields);

                        $alias = $entity->getAlias();

                        if (empty($alias)) {
                            $alias = $this->formModel->cleanAlias($entity->getName(), '', 10);
                            $entity->setAlias($alias);
                        }

                        if (!$entity->getId()) {
                            // Set timestamps because this is a new clone
                            $this->formModel->setTimestamps($entity, true, false);
                        }

                        // save the form first so that new fields are available to actions
                        // use the repository method to not trigger listeners twice
                        try {
                            $this->formRepository->saveEntity($entity);

                            if (count($actions)) {
                                // Now set and persist the actions
                                $this->formModel->setActions($entity, $actions);
                            }

                            // Delete deleted actions
                            $this->formModel->deleteActions($entity, $deletedActions);

                            // Persist and execute listeners
                            $this->formModel->saveEntity($entity, $this->getFormButton($form, ['buttons', 'save'])->isClicked());

                            // Reset objectId to entity ID (can be session ID in case of cloned entity)
                            $objectId = $entity->getId();

                            $this->addFlashMessage(
                                'mailvotech.core.notice.updated',
                                [
                                    '%name%'      => $entity->getName(),
                                    '%menu_link%' => 'mailvotech_form_index',
                                    '%url%'       => $this->generateUrl(
                                        'mailvotech_form_action',
                                        [
                                            'objectAction' => 'edit',
                                            'objectId'     => $entity->getId(),
                                        ]
                                    ),
                                ]
                            );

                            if ($this->getFormButton($form, ['buttons', 'save'])->isClicked()) {
                                $viewParameters = [
                                    'objectAction' => 'view',
                                    'objectId'     => $entity->getId(),
                                ];
                                $returnUrl = $this->generateUrl('mailvotech_form_action', $viewParameters);
                                $template  = 'MailVotech\FormBundle\Controller\FormController::viewAction';
                            }
                        } catch (ValidationException $ex) {
                            $form->addError(
                                new FormError(
                                    $ex->getMessage()
                                )
                            );
                            $valid = false;
                        }
                    }
                }
            } else {
                // unlock the entity
                $this->formModel->unlockEntity($entity);

                $viewParameters = ['page' => $page];
                $returnUrl      = $this->generateUrl('mailvotech_form_index', $viewParameters);
                $template       = 'MailVotech\FormBundle\Controller\FormController::indexAction';
            }

            if ($cancelled || ($valid && $this->getFormButton($form, ['buttons', 'save'])->isClicked())) {
                // remove fields from session
                $this->clearSessionComponents($request, $objectId);

                // Clear session items in case columns changed
                $session->remove('mailvotech.formresult.'.$entity->getId().'.orderby');
                $session->remove('mailvotech.formresult.'.$entity->getId().'.orderbydir');
                $session->remove('mailvotech.formresult.'.$entity->getId().'.filters');

                return $this->postActionRedirect(
                    array_merge(
                        $postActionVars,
                        [
                            'returnUrl'       => $returnUrl,
                            'viewParameters'  => $viewParameters,
                            'contentTemplate' => $template,
                        ]
                    )
                );
            }

            if ($valid && $this->isButtonClicked($form, 'apply')) {
                // Rebuild everything to include new ids
                $reorder    = true;

                // Rebuild the form with new action so that apply doesn't keep creating a clone
                $action = $this->generateUrl('mailvotech_form_action', ['objectAction' => 'edit', 'objectId' => $entity->getId()]);
                $form   = $this->formModel->createForm($entity, $this->formFactory, $action);
            }
        } else {
            // lock the entity
            $this->formModel->lockEntity($entity);
        }

        if (!$form->isSubmitted()) {
            $form->get('sessionId')->setData($objectId);
        }

        // Get field and action settings
        $availableFields = $this->fieldHelper->getChoiceList($customComponents['fields']);

        // clean slate
        $this->clearSessionComponents($request, $objectId);
        $this->alreadyMappedFieldCollector->removeAllForForm($objectId);

        // load existing fields into session
        $modifiedFields   = [];
        $existingFields   = $entity->getFields()->toArray();
        $fieldMap         = [];
        $submitButton     = false;

        foreach ($existingFields as $fieldId => $formField) {
            // Check to see if the field still exists

            if ('button' == $formField->getType()) {
                // submit button found
                $submitButton = true;
            }
            if ('button' !== $formField->getType() && !in_array($formField->getType(), $availableFields)) {
                continue;
            }

            $id    = $formField->getId();
            $field = $formField->convertToArray();

            if (!$id) {
                // Cloned entity
                $id = $field['id'] = $field['sessionId'] = $fieldMap[$fieldId] = 'new'.hash('sha1', uniqid(mt_rand()));
                if (isset($field['parent'])) {
                    $field['parent'] = $fieldMap[$field['parent']];
                }
            }

            unset($field['form']);

            if (isset($customComponents['fields'][$field['type']])) {
                // Set the custom parameters
                $field['customParameters'] = $customComponents['fields'][$field['type']];
            }

            $field['formId']     = $objectId;
            $modifiedFields[$id] = $field;

            if (!empty($field['mappedObject']) && !empty($field['mappedField']) && empty($field['parent'])) {
                $this->alreadyMappedFieldCollector->addField($objectId, $field['mappedObject'], $field['mappedField']);
            }
        }

        if (!$submitButton) { // means something deleted the submit button from the form
            // add a submit button
            $keyId = 'new'.hash('sha1', uniqid(mt_rand()));
            $field = new Field();

            $modifiedFields[$keyId]                    = $field->convertToArray();
            $modifiedFields[$keyId]['label']           = $this->translator->trans('mailvotech.core.form.submit');
            $modifiedFields[$keyId]['alias']           = 'submit';
            $modifiedFields[$keyId]['showLabel']       = 1;
            $modifiedFields[$keyId]['type']            = 'button';
            $modifiedFields[$keyId]['id']              = $keyId;
            $modifiedFields[$keyId]['inputAttributes'] = 'class="btn btn-ghost"';
            $modifiedFields[$keyId]['formId']          = $objectId;
            unset($modifiedFields[$keyId]['form']);
        }

        if (!empty($reorder)) {
            uasort(
                $modifiedFields,
                fn ($a, $b): int => $a['order'] <=> $b['order'] ?: $a['id'] <=> $b['id']
            );
        }

        $session->set('mailvotech.form.'.$objectId.'.fields.modified', $modifiedFields);
        $deletedFields = [];

        // Load existing actions into session
        $modifiedActions = [];
        $existingActions = $entity->getActions()->toArray();

        foreach ($existingActions as $formAction) {
            // Check to see if the action still exists
            if (!isset($customComponents['actions'][$formAction->getType()])) {
                continue;
            }

            $id     = $formAction->getId();
            $action = $formAction->convertToArray();

            if (!$id) {
                // Cloned entity so use a random Id instead
                $action['id'] = $id = 'new'.hash('sha1', uniqid(mt_rand()));
            }
            unset($action['form']);

            $modifiedActions[$id] = $action;
        }

        if (!empty($reorder)) {
            uasort(
                $modifiedActions,
                fn ($a, $b): int => $a['order'] <=> $b['order']
            );
        }

        $session->set('mailvotech.form.'.$objectId.'.actions.modified', $modifiedActions);
        $deletedActions = [];

        return $this->delegateView(
            [
                'viewParameters' => [
                    'fields'             => $availableFields,
                    'formFields'         => $modifiedFields,
                    'deletedFields'      => $deletedFields,
                    'mappedFields'       => $this->mappedObjectCollector->buildCollection(...$entity->getMappedFieldObjects()),
                    'formActions'        => $modifiedActions,
                    'deletedActions'     => $deletedActions,
                    'viewOnlyFields'     => $customComponents['viewOnlyFields'],
                    'actions'            => $customComponents['choices'],
                    'actionSettings'     => $customComponents['actions'],
                    'fieldSettings'      => $customComponents['fields'],
                    'tmpl'               => $request->isXmlHttpRequest() ? $request->get('tmpl', 'index') : 'index',
                    'activeForm'         => $entity,
                    'form'               => $form->createView(),
                    'forceTypeSelection' => $forceTypeSelection,
                    'inBuilder'          => true,
                ],
                'contentTemplate' => '@MailVotechForm/Builder/index.html.twig',
                'passthroughVars' => [
                    'activeLink'    => '#mailvotech_form_index',
                    'mailvotechContent' => 'form',
                    'route'         => $this->generateUrl(
                        'mailvotech_form_action',
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
     * Clone an entity.
     *
     * @param int $objectId
     */
    public function cloneAction(Request $request, $objectId): Response
    {
        /** @var Form $entity */
        $entity = $this->formModel->getEntity($objectId);

        if (null != $entity) {
            if (!$this->security->isGranted('form:forms:create')
                || !$this->security->hasEntityAccess(
                    'form:forms:viewown',
                    'form:forms:viewother',
                    $entity->getCreatedBy()
                )
            ) {
                $this->throwAccessDenied();
            }

            $entity = clone $entity;
            $entity->setIsPublished(false);

            // Clone the forms's fields
            $fields = $entity->getFields()->toArray();
            /** @var Field $field */
            foreach ($fields as $field) {
                $fieldClone = clone $field;
                $fieldClone->setForm($entity);
                $fieldClone->setSessionId(null);
                $entity->addField($field->getId(), $fieldClone);
            }

            // Clone the forms's actions
            $actions = $entity->getActions()->toArray();
            /** @var \MailVotech\FormBundle\Entity\Action $action */
            foreach ($actions as $action) {
                $actionClone = clone $action;
                $actionClone->setForm($entity);
                $entity->addAction($action->getId(), $actionClone);
            }
        }

        return $this->editAction($request, $entity, true);
    }

    /**
     * Gives a preview of the form.
     *
     * @param int $objectId
     */
    public function previewAction($objectId, ThemeHelper $themeHelper, AssetsHelper $assetsHelper, AnalyticsHelper $analyticsHelper): Response
    {
        $form  = $this->formModel->getEntity($objectId);

        if (null === $form) {
            $html =
                '<h1>'.
                $this->translator->trans('mailvotech.form.error.notfound', ['%id%' => $objectId], 'flashes').
                '</h1>';
        } elseif (!$this->security->hasEntityAccess(
            'form:forms:editown',
            'form:forms:editother',
            $form->getCreatedBy()
        )
        ) {
            $html = '<h1>'.$this->translator->trans('mailvotech.core.error.accessdenied', [], 'flashes').'</h1>';
        } else {
            $html = $this->formModel->getContent($form, true, false);
        }

        $this->formModel->populateValuesWithGetParameters($form, $html);

        $viewParams = [
            'content'     => $html,
            'stylesheets' => [],
            'name'        => $form->getName(),
            'metaRobots'  => '<meta name="robots" content="index">',
        ];

        if ($form->getNoIndex()) {
            $viewParams['metaRobots'] = '<meta name="robots" content="noindex">';
        }

        // Use form specific template or system-wide default theme
        $template = $form->getTemplate() ?? $this->coreParametersHelper->get('theme');
        if (!empty($template)) {
            $theme = $themeHelper->getTheme($template);
            if ($theme->getTheme() != $template) {
                $config = $theme->getConfig();
                if (in_array('form', $config['features'])) {
                    $template = $theme->getTheme();
                } else {
                    $template = null;
                }
            }
        }

        $viewParams['template'] = $template;

        if (!empty($template)) {
            $logicalName     = $themeHelper->checkForTwigTemplate('@themes/'.$template.'/html/form.html.twig');

            $analytics = $analyticsHelper->getCode();

            if (!empty($analytics)) {
                $assetsHelper->addCustomDeclaration($analytics);
            }
            if ($form->getNoIndex()) {
                $assetsHelper->addCustomDeclaration('<meta name="robots" content="noindex">');
            }

            return new Response($themeHelper->renderThemeTemplate($logicalName, $viewParams));
        }

        return $this->render('@MailVotechForm/form.html.twig', $viewParams);
    }

    /**
     * Deletes the entity.
     *
     * @param int $objectId
     */
    public function deleteAction(Request $request, $objectId): Response
    {
        $page      = $request->getSession()->get('mailvotech.form.page', 1);
        $returnUrl = $this->generateUrl('mailvotech_form_index', ['page' => $page]);
        $flashes   = [];

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MailVotech\FormBundle\Controller\FormController::indexAction',
            'passthroughVars' => [
                'activeLink'    => '#mailvotech_form_index',
                'mailvotechContent' => 'form',
            ],
        ];

        if (Request::METHOD_POST === $request->getMethod()) {
            $entity = $this->formModel->getEntity($objectId);

            if (null === $entity) {
                $flashes[] = [
                    'type'    => 'error',
                    'msg'     => 'mailvotech.form.error.notfound',
                    'msgVars' => ['%id%' => $objectId],
                ];
            } elseif (!$this->security->hasEntityAccess(
                'form:forms:deleteown',
                'form:forms:deleteother',
                $entity->getCreatedBy()
            )
            ) {
                $this->throwAccessDenied();
            } elseif ($this->formModel->isLocked($entity)) {
                return $this->isLocked($postActionVars, $entity, 'form.form');
            }

            $this->formModel->deleteEntity($entity);

            $identifier = $this->translator->trans($entity->getName());
            $flashes[]  = [
                'type'    => 'notice',
                'msg'     => 'mailvotech.core.notice.deleted',
                'msgVars' => [
                    '%name%' => $identifier,
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
        $page      = $request->getSession()->get('mailvotech.form.page', 1);
        $returnUrl = $this->generateUrl('mailvotech_form_index', ['page' => $page]);
        $flashes   = [];

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MailVotech\FormBundle\Controller\FormController::indexAction',
            'passthroughVars' => [
                'activeLink'    => '#mailvotech_form_index',
                'mailvotechContent' => 'form',
            ],
        ];

        if (Request::METHOD_POST === $request->getMethod()) {
            $ids       = json_decode($request->query->get('ids', ''));
            $deleteIds = [];

            // Loop over the IDs to perform access checks pre-delete
            foreach ($ids as $objectId) {
                $objectId = (int) $objectId;
                $entity   = $this->formModel->getEntity($objectId);

                if (null === $entity) {
                    $flashes[] = [
                        'type'    => 'error',
                        'msg'     => 'mailvotech.form.error.notfound',
                        'msgVars' => ['%id%' => $objectId],
                    ];
                } elseif (!$this->security->hasEntityAccess(
                    'form:forms:deleteown',
                    'form:forms:deleteother',
                    $entity->getCreatedBy()
                )
                ) {
                    $flashes[] = $this->getAccessDeniedFlash();
                } elseif ($this->formModel->isLocked($entity)) {
                    $flashes[] = $this->isLocked($postActionVars, $entity, 'form.form', true);
                } else {
                    $deleteIds[] = $objectId;
                }
            }

            // Delete everything we are able to
            if ([] !== $deleteIds) {
                $entities = $this->formModel->deleteEntities($deleteIds);

                $flashes[] = [
                    'type'    => 'notice',
                    'msg'     => 'mailvotech.form.notice.batch_deleted',
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
     * Clear field and actions from the session.
     */
    public function clearSessionComponents(Request $request, $sessionId): void
    {
        $session = $request->getSession();
        $session->remove('mailvotech.form.'.$sessionId.'.fields.modified');
        $session->remove('mailvotech.form.'.$sessionId.'.fields.deleted');
        $session->remove('mailvotech.form.'.$sessionId.'.actions.modified');
        $session->remove('mailvotech.form.'.$sessionId.'.actions.deleted');

        $this->alreadyMappedFieldCollector->removeAllForForm((string) $sessionId);
    }

    public function batchRebuildHtmlAction(Request $request): Response
    {
        $page      = $request->getSession()->get('mailvotech.form.page', 1);
        $returnUrl = $this->generateUrl('mailvotech_form_index', ['page' => $page]);
        $flashes   = [];

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MailVotech\FormBundle\Controller\FormController::indexAction',
            'passthroughVars' => [
                'activeLink'    => '#mailvotech_form_index',
                'mailvotechContent' => 'form',
            ],
        ];

        if ('POST' === $request->getMethod()) {
            $ids   = json_decode($request->query->get('ids', ''));
            $count = 0;
            // Loop over the IDs to perform access checks pre-delete
            foreach ($ids as $objectId) {
                $entity = $this->formModel->getEntity($objectId);

                if (null === $entity) {
                    $flashes[] = [
                        'type'    => 'error',
                        'msg'     => 'mailvotech.form.error.notfound',
                        'msgVars' => ['%id%' => $objectId],
                    ];
                } elseif (!$this->security->hasEntityAccess(
                    'form:forms:editown',
                    'form:forms:editother',
                    $entity->getCreatedBy()
                )
                ) {
                    $flashes[] = $this->getAccessDeniedFlash();
                } elseif ($this->formModel->isLocked($entity)) {
                    $flashes[] = $this->isLocked($postActionVars, $entity, 'form.form', true);
                } else {
                    $this->formModel->generateHtml($entity);
                    ++$count;
                }
            }

            $flashes[] = [
                'type'    => 'notice',
                'msg'     => 'mailvotech.form.notice.batch_html_generated',
                'msgVars' => [
                    '%count%'     => $count,
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

    public function getModelName(): string
    {
        return 'form';
    }

    protected function getDefaultOrderDirection(): string
    {
        return 'DESC';
    }
}
