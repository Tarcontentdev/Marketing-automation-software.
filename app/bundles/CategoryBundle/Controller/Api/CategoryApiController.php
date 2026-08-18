<?php

namespace MailVotech\CategoryBundle\Controller\Api;

use Doctrine\Persistence\ManagerRegistry;
use MailVotech\ApiBundle\Controller\CommonApiController;
use MailVotech\ApiBundle\Helper\EntityResultHelper;
use MailVotech\CategoryBundle\Entity\Category;
use MailVotech\CategoryBundle\Model\CategoryModel;
use MailVotech\CoreBundle\Factory\ModelFactory;
use MailVotech\CoreBundle\Helper\AppVersion;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\CoreBundle\Translation\Translator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

/**
 * @extends CommonApiController<Category>
 */
final class CategoryApiController extends CommonApiController
{
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
        CategoryModel $categoryModel,
    ) {
        $this->model            = $categoryModel;
        $this->entityClass      = Category::class;
        $this->entityNameOne    = 'category';
        $this->entityNameMulti  = 'categories';
        $this->serializerGroups = ['categoryDetails'];

        parent::__construct($security, $translator, $entityResultHelper, $router, $formFactory, $appVersion, $requestStack, $doctrine, $modelFactory, $dispatcher, $coreParametersHelper);
    }

    /**
     * Checks if user has permission to access retrieved entity.
     *
     * @param Category $entity
     * @param string   $action view|create|edit|publish|delete
     */
    protected function checkEntityAccess($entity, $action = 'view'): bool
    {
        if (!$bundle = $entity->getBundle()) {
            $bundle = 'category';
        }

        $permissionBase = $this->permissionBase;

        if ($this->security->checkPermissionExists($bundle.':categories:'.$action)) {
            // An entity specific category permission rule exists
            $permissionBase = $bundle.':categories';
        }

        if ('create' != $action) {
            $ownPerm   = "{$permissionBase}:{$action}own";
            $otherPerm = "{$permissionBase}:{$action}other";

            return $this->security->hasEntityAccess($ownPerm, $otherPerm, $entity->getCreatedBy());
        }

        return $this->security->isGranted("{$permissionBase}:create");
    }

    /**
     * @return array<string, mixed>
     */
    protected function getEntityFormOptions(): array
    {
        return ['show_bundle_select' => true];
    }
}
