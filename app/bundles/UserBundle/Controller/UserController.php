<?php

declare(strict_types=1);

namespace MailVotech\UserBundle\Controller;

use JMS\Serializer\SerializerInterface;
use MailVotech\CoreBundle\Controller\FormController;
use MailVotech\CoreBundle\Entity\AuditLogRepository;
use MailVotech\CoreBundle\Factory\PageHelperFactoryInterface;
use MailVotech\CoreBundle\Helper\InputHelper;
use MailVotech\CoreBundle\Helper\IpLookupHelper;
use MailVotech\CoreBundle\Helper\LanguageHelper;
use MailVotech\CoreBundle\Model\AuditLogModel;
use MailVotech\CoreBundle\Model\FormModel;
use MailVotech\EmailBundle\Helper\MailHelper;
use MailVotech\UserBundle\Entity\Role;
use MailVotech\UserBundle\Entity\RoleRepository;
use MailVotech\UserBundle\Entity\User;
use MailVotech\UserBundle\Form\Type\ContactType;
use MailVotech\UserBundle\Form\Type\UserInviteType;
use MailVotech\UserBundle\Model\RoleModel;
use MailVotech\UserBundle\Model\UserModel;
use MailVotech\UserBundle\Security\SAML\Helper as SAMLHelper;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\Required;

final class UserController extends FormController
{
    private RoleRepository $roleRepository;

    private AuditLogRepository $auditLogRepository;

    private UserModel $userModel;

    private AuditLogModel $auditLogModel;

    #[Required]
    public function autowireUserController(
        UserModel $userModel,
        AuditLogModel $auditLogModel,
        RoleModel $roleModel,
        AuditLogRepository $auditLogRepository,
        RoleRepository $roleRepository,
    ): void {
        $this->userModel = $userModel;
        $this->auditLogModel = $auditLogModel;
        $this->auditLogRepository = $auditLogRepository;
        $this->roleRepository = $roleRepository;
    }

    /**
     * Generate's default user list.
     */
    public function indexAction(Request $request, PageHelperFactoryInterface $pageHelperFactory, int $page = 1): JsonResponse|Response
    {
        if (!$this->security->isGranted('user:users:view')) {
            $this->throwAccessDenied();
        }
        $pageHelper = $pageHelperFactory->make('mailvotech.user', $page);

        $this->setListFilters();

        $currentUserId = $this->user->getId();
        $limit         = $pageHelper->getLimit();
        $start         = $pageHelper->getStart();
        $orderBy       = $request->getSession()->get('mailvotech.user.orderby', 'u.lastName, u.firstName, u.username');
        $orderByDir    = $request->getSession()->get('mailvotech.user.orderbydir', 'ASC');
        $search        = $request->get('search', $request->getSession()->get('mailvotech.user.filter', ''));
        $search        = html_entity_decode($search);
        $request->getSession()->set('mailvotech.user.filter', $search);

        // do some default filtering
        $filter = ['string' => $search, 'force' => ''];
        $tmpl   = $request->isXmlHttpRequest() ? $request->get('tmpl', 'index') : 'index';
        $users  = $this->userModel->getEntities(
            [
                'start'      => $start,
                'limit'      => $limit,
                'filter'     => $filter,
                'orderBy'    => $orderBy,
                'orderByDir' => $orderByDir,
            ]);

        // Check to see if the number of pages match the number of users
        $count = count($users);
        if ($count && $count < ($start + 1)) {
            // the number of entities are now less then the current page so redirect to the last page
            $lastPage = $pageHelper->countPage($count);
            $pageHelper->rememberPage($lastPage);
            $returnUrl = $this->generateUrl('mailvotech_user_index', ['page' => $lastPage]);

            return $this->postActionRedirect([
                'returnUrl'      => $returnUrl,
                'viewParameters' => [
                    'page' => $lastPage,
                    'tmpl' => $tmpl,
                ],
                'contentTemplate' => 'MailVotech\UserBundle\Controller\UserController::indexAction',
                'passthroughVars' => [
                    'activeLink'    => '#mailvotech_user_index',
                    'mailvotechContent' => 'user',
                ],
            ]);
        }

        $pageHelper->rememberPage($page);

        $inviteForm = null;
        if ($this->security->isGranted('user:users:create')) {
            $action     = $this->generateUrl('mailvotech_user_action', ['objectAction' => 'invite']);
            $inviteForm = $this->createForm(UserInviteType::class, [], ['action' => $action]);
        }

        return $this->delegateView([
            'viewParameters'  => [
                'items'         => $users,
                'searchValue'   => $search,
                'page'          => $page,
                'limit'         => $limit,
                'tmpl'          => $tmpl,
                'currentUserId' => $currentUserId,
                'permissions'   => [
                    'create' => $this->security->isGranted('user:users:create'),
                    'edit'   => $this->security->isGranted('user:users:editother'),
                    'delete' => $this->security->isGranted('user:users:deleteother'),
                ],
                'inviteForm'    => $inviteForm ? $inviteForm->createView() : null,
            ],
            'contentTemplate' => '@MailVotechUser/User/list.html.twig',
            'passthroughVars' => [
                'route'         => $this->generateUrl('mailvotech_user_index', ['page' => $page]),
                'mailvotechContent' => 'user',
            ],
        ]);
    }

    /**
     * Generate's form and processes new post data.
     */
    public function inviteAction(Request $request, UserModel $model): JsonResponse|Response
    {
        if (!$this->security->isGranted('user:users:create')) {
            $this->throwAccessDenied();
        }
        $action = $this->generateUrl('mailvotech_user_action', ['objectAction' => 'invite']);
        $form   = $this->createForm(UserInviteType::class, [], ['action' => $action]);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            $response = null;

            if ($form->isSubmitted() && $form->isValid()) {
                $data  = $form->getData();
                $email = $data['email'];
                $role  = $data['role'];
                \assert(is_string($email));
                \assert($role instanceof Role);

                $model->createInvite($email, $role);
                $this->addFlashMessage('mailvotech.user.invite.flash.sent', ['%email%' => $email], 'notice', 'flashes');

                if ($request->isXmlHttpRequest()) {
                    $response = new JsonResponse([
                        'closeModal' => 1,
                        'redirect'   => $this->generateUrl('mailvotech_user_index'),
                    ]);
                } else {
                    $response = $this->redirectToRoute('mailvotech_user_index');
                }
            }

            return $response ?? $this->delegateView([
                'viewParameters' => [
                    'form' => $form->createView(),
                ],
                'contentTemplate' => '@MailVotechUser/User/invite.html.twig',
                'passthroughVars' => [
                    'route'              => $action,
                    'mailvotechContent'      => 'user',
                    'header'             => $this->translator->trans('mailvotech.user.invite.title'),
                    'target'             => '#InviteUserModal .modal-body-content',
                    'updateModalContent' => 1,
                ],
            ]);
        }

        return $this->delegateView([
            'viewParameters' => [
                'form' => $form->createView(),
            ],
            'contentTemplate' => '@MailVotechUser/User/invite.html.twig',
            'passthroughVars' => [
                'route'         => $action,
                'mailvotechContent' => 'user',
                'header'        => $this->translator->trans('mailvotech.user.invite.title'),
            ],
        ]);
    }

    public function newAction(Request $request, LanguageHelper $languageHelper, SAMLHelper $samlHelper): JsonResponse|Response
    {
        if (!$this->security->isGranted('user:users:create')) {
            $this->throwAccessDenied();
        }

        // retrieve the user entity
        $user = $this->userModel->getEntity();

        // get the user form factory
        $action   = $this->generateUrl('mailvotech_user_action', ['objectAction' => 'new']);
        $form     = $this->userModel->createForm($user, $this->formFactory, $action);
        $response = null;

        // Check for a submitted form and process it
        if ('POST' === $request->getMethod()) {
            $response = $this->handleNewUserPost($request, $languageHelper, $samlHelper, $user, $form);
        }

        return $response ?? $this->renderNewUserForm($form, $action);
    }

    private function handleNewUserPost(Request $request, LanguageHelper $languageHelper, SAMLHelper $samlHelper, User $user, FormInterface $form): JsonResponse|Response|null
    {
        $response  = null;
        $cancelled = $this->isFormCancelled($form);
        $valid     = false;

        if (!$cancelled) {
            $valid = $this->saveNewUserIfValid($request, $languageHelper, $user, $form);
        }

        if ($cancelled || ($valid && $this->getFormButton($form, ['buttons', 'save'])->isClicked())) {
            $response = $this->postActionRedirect([
                'returnUrl'       => $this->generateUrl('mailvotech_user_index'),
                'viewParameters'  => ['page' => $request->getSession()->get('mailvotech.user.page', 1), 'isSamlUser' => false],
                'contentTemplate' => 'MailVotech\UserBundle\Controller\UserController::indexAction',
                'passthroughVars' => [
                    'activeLink'    => '#mailvotech_user_index',
                    'mailvotechContent' => 'user',
                ],
            ]);
        } elseif ($valid) {
            $response = $this->editAction($request, $languageHelper, $samlHelper, $user->getId(), true);
        }

        return $response;
    }

    private function saveNewUserIfValid(Request $request, LanguageHelper $languageHelper, User $user, FormInterface $form): bool
    {
        $formUser          = $request->request->all()['user'] ?? [];
        $submittedPassword = $formUser['plainPassword']['password'] ?? null;
        $password          = $this->userModel->checkNewPassword($user, $submittedPassword);
        $valid             = $this->isFormValid($form);

        if ($valid) {
            $user->setPassword($password);
            $this->userModel->saveEntity($user);
            $this->loadNewUserLocale($languageHelper, $user);

            $this->addFlashMessage('mailvotech.core.notice.created', [
                '%name%'      => $user->getName(),
                '%menu_link%' => 'mailvotech_user_index',
                '%url%'       => $this->generateUrl('mailvotech_user_action', [
                    'objectAction' => 'edit',
                    'objectId'     => $user->getId(),
                ]),
            ]);
        }

        return $valid;
    }

    private function loadNewUserLocale(LanguageHelper $languageHelper, User $user): void
    {
        $installedLanguages = $languageHelper->getSupportedLanguages();

        if ($user->getLocale() && !array_key_exists($user->getLocale(), $installedLanguages)) {
            $fetchLanguage = $languageHelper->extractLanguagePackage($user->getLocale());

            if ($fetchLanguage['error']) {
                $user->setLocale(null);
                $this->userModel->saveEntity($user);
                $this->addFlashMessage(
                    $fetchLanguage['message'] ?? 'mailvotech.core.could.not.set.language',
                    $fetchLanguage['vars'] ?? []
                );
            }
        }
    }

    private function renderNewUserForm(FormInterface $form, string $action): JsonResponse|Response
    {
        return $this->delegateView([
            'viewParameters'  => ['form' => $form->createView(), 'isSamlUser' => false],
            'contentTemplate' => '@MailVotechUser/User/form.html.twig',
            'passthroughVars' => [
                'activeLink'    => '#mailvotech_user_new',
                'route'         => $action,
                'mailvotechContent' => 'user',
            ],
        ]);
    }

    /**
     * Generates edit form and processes post data.
     *
     * @param int  $objectId
     * @param bool $ignorePost
     */
    public function editAction(Request $request, LanguageHelper $languageHelper, SAMLHelper $samlHelper, $objectId, $ignorePost = false): Response
    {
        if (!$this->security->isGranted('user:users:edit')) {
            $this->throwAccessDenied();
        }
        $user = $this->userModel->getEntity($objectId);
        if (null === $user) {
            return $this->postActionRedirect([
                'returnUrl'       => $this->generateUrl('mailvotech_user_index'),
                'flashes'         => [
                    [
                        'type'    => 'error',
                        'msg'     => 'mailvotech.user.user.error.notfound',
                        'msgVars' => ['%id%' => $objectId],
                    ],
                ],
            ]);
        }

        $oldEmail = $user->getEmail();

        $userActivity       = $this->auditLogRepository->getLogsForUser($user);
        $users              = $this->userModel->getEntities();
        $roles              = $this->roleRepository->getEntities();

        // set the page we came from
        $page = $request->getSession()->get('mailvotech.user.page', 1);

        // set the return URL
        $returnUrl = $this->generateUrl('mailvotech_user_index', ['page' => $page]);

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MailVotech\UserBundle\Controller\UserController::indexAction',
            'passthroughVars' => [
                'activeLink'    => '#mailvotech_user_index',
                'mailvotechContent' => 'user',
            ],
        ];

        if ($this->userModel->isLocked($user)) {
            // deny access if the entity is locked
            return $this->isLocked($postActionVars, $user, 'user.user');
        }

        $action = $this->generateUrl('mailvotech_user_action', ['objectAction' => 'edit', 'objectId' => $objectId]);
        $form   = $this->userModel->createForm($user, $this->formFactory, $action);

        $isSamlUser    = $samlHelper->isSamlSession();
        if ($isSamlUser) {
            $form->remove('plainPassword');
        }

        // /Check for a submitted form and process it
        if (!$ignorePost && 'POST' === $request->getMethod()) {
            $valid = false;

            if (!$cancelled = $this->isFormCancelled($form)) {
                // check to see if the password needs to be rehashed
                $formUser          = $request->request->all()['user'] ?? [];
                $submittedPassword = $formUser['plainPassword']['password'] ?? null;
                $password          = $this->userModel->checkNewPassword($user, $submittedPassword);
                $newEmail          = $formUser['email'] ?? null;

                if ($valid = $this->isFormValid($form)) {
                    // form is valid so process the data
                    $user->setPassword($password);
                    $this->userModel->saveEntity($user, $this->getFormButton($form, ['buttons', 'save'])->isClicked());
                    if (!empty($submittedPassword)) {
                        $this->userModel->sendChangePasswordInfo($user);
                    }

                    if ($newEmail !== $oldEmail) {
                        $this->userModel->sendChangeEmailInfo($oldEmail, $user);
                    }

                    // check if the user's locale has been downloaded already, fetch it if not
                    $installedLanguages = $languageHelper->getSupportedLanguages();

                    if ($user->getLocale() && !array_key_exists($user->getLocale(), $installedLanguages)) {
                        $fetchLanguage = $languageHelper->extractLanguagePackage($user->getLocale());

                        // If there is an error, we need to reset the user's locale to the default
                        if ($fetchLanguage['error']) {
                            $user->setLocale(null);
                            $this->userModel->saveEntity($user);
                            $message     = 'mailvotech.core.could.not.set.language';
                            $messageVars = [];

                            if (isset($fetchLanguage['message'])) {
                                $message = $fetchLanguage['message'];
                            }

                            if (isset($fetchLanguage['vars'])) {
                                $messageVars = $fetchLanguage['vars'];
                            }

                            $this->addFlashMessage($message, $messageVars);
                        }
                    }

                    $this->addFlashMessage('mailvotech.core.notice.updated', [
                        '%name%'      => $user->getName(),
                        '%menu_link%' => 'mailvotech_user_index',
                        '%url%'       => $this->generateUrl('mailvotech_user_action', [
                            'objectAction' => 'edit',
                            'objectId'     => $user->getId(),
                        ]),
                    ]);
                }
            } else {
                // unlock the entity
                $this->userModel->unlockEntity($user);
            }

            if ($cancelled || ($valid && $this->getFormButton($form, ['buttons', 'save'])->isClicked())) {
                return $this->postActionRedirect($postActionVars);
            }
        } else {
            // lock the entity
            $this->userModel->lockEntity($user);
        }

        return $this->delegateView([
            'viewParameters'  => [
                'form'                   => $form->createView(),
                'logs'                   => $userActivity,
                'users'                  => $users,
                'roles'                  => $roles,
                'editAction'             => true,
                'isSamlUser'             => $isSamlUser,
            ],
            'contentTemplate' => '@MailVotechUser/User/form.html.twig',
            'passthroughVars' => [
                'activeLink'    => '#mailvotech_user_index',
                'route'         => $action,
                'mailvotechContent' => 'user',
            ],
        ]);
    }

    /**
     * Deletes a user object.
     *
     * @param int $objectId
     */
    public function deleteAction(Request $request, $objectId): Response
    {
        if (!$this->security->isGranted('user:users:delete')) {
            $this->throwAccessDenied();
        }

        $currentUser    = $this->user;
        $page           = $request->getSession()->get('mailvotech.user.page', 1);
        $returnUrl      = $this->generateUrl('mailvotech_user_index', ['page' => $page]);
        $success        = 0;
        $flashes        = [];
        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MailVotech\UserBundle\Controller\UserController::indexAction',
            'passthroughVars' => [
                'activeLink'    => '#mailvotech_user_index',
                'route'         => $returnUrl,
                'success'       => $success,
                'mailvotechContent' => 'user',
            ],
        ];
        if ('POST' === $request->getMethod()) {
            // ensure the user logged in is not getting deleted
            if ((int) $currentUser->getId() !== (int) $objectId) {
                $entity = $this->userModel->getEntity($objectId);

                if (null === $entity) {
                    $flashes[] = [
                        'type'    => 'error',
                        'msg'     => 'mailvotech.user.user.error.notfound',
                        'msgVars' => ['%id%' => $objectId],
                    ];
                } elseif ($this->userModel->isLocked($entity)) {
                    return $this->isLocked($postActionVars, $entity, 'user.user');
                } else {
                    $this->userModel->deleteEntity($entity);
                    $name      = $entity->getName();
                    $flashes[] = [
                        'type'    => 'notice',
                        'msg'     => 'mailvotech.core.notice.deleted',
                        'msgVars' => [
                            '%name%' => $name,
                            '%id%'   => $objectId,
                        ],
                    ];
                }
            } else {
                $flashes[] = [
                    'type' => 'error',
                    'msg'  => 'mailvotech.user.user.error.cannotdeleteself',
                ];
            }
        } // else don't do anything

        return $this->postActionRedirect(
            array_merge($postActionVars, [
                'flashes' => $flashes,
            ])
        );
    }

    /**
     * Contacts a user.
     *
     * @param int $objectId
     */
    public function contactAction(Request $request, SerializerInterface $serializer, MailHelper $mailer, IpLookupHelper $ipLookupHelper, $objectId): Response|\Symfony\Component\HttpFoundation\RedirectResponse
    {
        $user  = $this->userModel->getEntity($objectId);

        // user not found
        if (null === $user) {
            return $this->postActionRedirect([
                'returnUrl'       => $this->generateUrl('mailvotech_dashboard_index'),
                'contentTemplate' => 'MailVotech\UserBundle\Controller\UserController::contactAction',
                'flashes'         => [
                    [
                        'type'    => 'error',
                        'msg'     => 'mailvotech.user.user.error.notfound',
                        'msgVars' => ['%id%' => $objectId],
                    ],
                ],
            ]);
        }

        $action = $this->generateUrl('mailvotech_user_action', ['objectAction' => 'contact', 'objectId' => $objectId]);
        $form   = $this->createForm(ContactType::class, [], ['action' => $action]);

        $currentUser = $this->user;

        if ('POST' === $request->getMethod()) {
            $contact   = $request->request->all()['contact'] ?? [];
            $formUrl   = $contact['returnUrl'] ?? '';
            $returnUrl = $formUrl ? urldecode($formUrl) : $this->generateUrl('mailvotech_dashboard_index');
            $valid     = false;

            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    $subject = InputHelper::clean($form->get('msg_subject')->getData());
                    $body    = InputHelper::clean($form->get('msg_body')->getData());

                    $mailer->setFrom($currentUser->getEmail(), $currentUser->getName());
                    $mailer->setSubject($subject);
                    $mailer->setTo($user->getEmail(), $user->getName());
                    $mailer->setBody($body);
                    $mailer->send();

                    $reEntity = $form->get('entity')->getData();
                    if (empty($reEntity)) {
                        $bundle   = $object   = 'user';
                        $entityId = $user->getId();
                    } else {
                        $bundle = $object = $reEntity;
                        if (strpos($reEntity, ':')) {
                            [$bundle, $object] = explode(':', $reEntity);
                        }
                        $entityId = $form->get('id')->getData();
                    }

                    $details = $serializer->serialize([
                        'from'    => $currentUser->getName(),
                        'to'      => $user->getName(),
                        'subject' => $subject,
                        'message' => $body,
                    ], 'json');

                    $log = [
                        'bundle'    => $bundle,
                        'object'    => $object,
                        'objectId'  => $entityId,
                        'action'    => 'communication',
                        'details'   => $details,
                        'ipAddress' => $ipLookupHelper->getIpAddressFromRequest(),
                    ];
                    $this->auditLogModel->writeToLog($log);

                    $this->addFlashMessage('mailvotech.user.user.notice.messagesent', ['%name%' => $user->getName()]);
                }
            }
            if ($cancelled || $valid) {
                return $this->redirect($returnUrl);
            }
        } else {
            $reEntityId = (int) $request->get('id');
            $reSubject  = InputHelper::clean($request->get('subject'));
            $returnUrl  = InputHelper::clean($request->get('returnUrl', $this->generateUrl('mailvotech_dashboard_index')));
            $reEntity   = InputHelper::clean($request->get('entity'));

            $form->get('entity')->setData($reEntity);
            $form->get('id')->setData($reEntityId);
            $form->get('returnUrl')->setData($returnUrl);

            if (!empty($reEntity) && !empty($reEntityId)) {
                /** @var FormModel<object> $model */
                $model  = $this->getModel($reEntity);
                $entity = $model->getEntity($reEntityId);

                if (null !== $entity) {
                    $subject = $model->getUserContactSubject($reSubject, $entity);
                    $form->get('msg_subject')->setData($subject);
                }
            }
        }

        return $this->delegateView([
            'viewParameters' => [
                'form' => $form->createView(),
                'user' => $user,
            ],
            'contentTemplate' => '@MailVotechUser/User/contact.html.twig',
            'passthroughVars' => [
                'route'         => $action,
                'mailvotechContent' => 'user',
            ],
        ]);
    }

    /**
     * Deletes a group of entities.
     */
    public function batchDeleteAction(Request $request): Response
    {
        $page      = $request->getSession()->get('mailvotech.user.page', 1);
        $returnUrl = $this->generateUrl('mailvotech_user_index', ['page' => $page]);
        $flashes   = [];

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MailVotech\UserBundle\Controller\UserController::indexAction',
            'passthroughVars' => [
                'activeLink'    => '#mailvotech_user_index',
                'mailvotechContent' => 'user',
            ],
        ];

        if (Request::METHOD_POST === $request->getMethod()) {
            $ids         = json_decode($request->query->get('ids', ''));
            $deleteIds   = [];
            $currentUser = $this->user;

            // Loop over the IDs to perform access checks pre-delete
            foreach ($ids as $objectId) {
                $entity = $this->userModel->getEntity($objectId);

                if ((int) $currentUser->getId() === (int) $objectId) {
                    $flashes[] = [
                        'type' => 'error',
                        'msg'  => 'mailvotech.user.user.error.cannotdeleteself',
                    ];
                } elseif (null === $entity) {
                    $flashes[] = [
                        'type'    => 'error',
                        'msg'     => 'mailvotech.user.user.error.notfound',
                        'msgVars' => ['%id%' => $objectId],
                    ];
                } elseif (!$this->security->isGranted('user:users:delete')) {
                    $flashes[] = $this->getAccessDeniedFlash();
                } elseif ($this->userModel->isLocked($entity)) {
                    $flashes[] = $this->isLocked($postActionVars, $entity, 'user', true);
                } else {
                    $deleteIds[] = $objectId;
                }
            }

            // Delete everything we are able to
            if ([] !== $deleteIds) {
                $entities = $this->userModel->deleteEntities($deleteIds);

                $flashes[] = [
                    'type'    => 'notice',
                    'msg'     => 'mailvotech.user.user.notice.batch_deleted',
                    'msgVars' => [
                        '%count%' => count($entities),
                    ],
                ];
            }
        } // else don't do anything

        return $this->postActionRedirect(
            array_merge($postActionVars, [
                'flashes' => $flashes,
            ])
        );
    }
}
