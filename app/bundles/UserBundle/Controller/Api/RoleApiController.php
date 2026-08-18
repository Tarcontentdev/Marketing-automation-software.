<?php

namespace MailVotech\UserBundle\Controller\Api;

use Doctrine\Persistence\ManagerRegistry;
use MailVotech\ApiBundle\Controller\CommonApiController;
use MailVotech\ApiBundle\Helper\EntityResultHelper;
use MailVotech\CoreBundle\Factory\ModelFactory;
use MailVotech\CoreBundle\Helper\AppVersion;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\CoreBundle\Translation\Translator;
use MailVotech\UserBundle\Entity\Role;
use MailVotech\UserBundle\Model\RoleModel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

/**
 * @extends CommonApiController<Role>
 */
final class RoleApiController extends CommonApiController
{
    /**
     * @var RoleModel|null
     */
    protected $model;

    public function __construct(
        CorePermissions $security,
        Translator $translator,
        EntityResultHelper $entityResultHelper,
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        AppVersion $appVersion,
        RequestStack $requestStack,
        ManagerRegistry $doctrine,
        ModelFactory $modelFactory,
        EventDispatcherInterface $dispatcher,
        CoreParametersHelper $coreParametersHelper,
        RoleModel $roleModel,
    ) {
        $this->model            = $roleModel;
        $this->entityClass      = Role::class;
        $this->entityNameOne    = 'role';
        $this->entityNameMulti  = 'roles';
        $this->serializerGroups = ['roleDetails', 'publishDetails'];

        parent::__construct($security, $translator, $entityResultHelper, $router, $formFactory, $appVersion, $requestStack, $doctrine, $modelFactory, $dispatcher, $coreParametersHelper);
    }

    /**
     * @param Role                 $entity
     * @param FormInterface<mixed> $form
     * @param array<mixed>         $parameters
     * @param string               $action
     */
    protected function preSaveEntity(&$entity, $form, $parameters, $action = 'edit'): void
    {
        if (isset($parameters['rawPermissions'])) {
            $this->model->setRolePermissions($entity, $parameters['rawPermissions']);
        }
    }
}
