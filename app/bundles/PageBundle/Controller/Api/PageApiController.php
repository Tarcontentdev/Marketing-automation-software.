<?php

namespace MailVotech\PageBundle\Controller\Api;

use Doctrine\Persistence\ManagerRegistry;
use MailVotech\ApiBundle\Controller\CommonApiController;
use MailVotech\ApiBundle\Helper\EntityResultHelper;
use MailVotech\CoreBundle\Factory\ModelFactory;
use MailVotech\CoreBundle\Helper\AppVersion;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Helper\UserHelper;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\CoreBundle\Translation\Translator;
use MailVotech\PageBundle\Entity\Page;
use MailVotech\PageBundle\Model\PageModel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * @extends CommonApiController<Page>
 */
final class PageApiController extends CommonApiController
{
    /**
     * @var PageModel|null
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
        PageModel $pageModel,
    ) {
        $this->model            = $pageModel;
        $this->entityClass      = Page::class;
        $this->entityNameOne    = 'page';
        $this->entityNameMulti  = 'pages';
        $this->serializerGroups = ['pageDetails', 'categoryList', 'publishDetails'];
        $this->dataInputMasks   = ['customHtml' => 'html'];

        parent::__construct($security, $translator, $entityResultHelper, $router, $formFactory, $appVersion, $requestStack, $doctrine, $modelFactory, $dispatcher, $coreParametersHelper);
    }

    /**
     * Obtains a list of pages.
     */
    public function getEntitiesAction(Request $request, UserHelper $userHelper): Response
    {
        // get parent level only
        $this->listFilters[] = [
            'column' => 'p.variantParent',
            'expr'   => 'isNull',
        ];

        $this->listFilters[] = [
            'column' => 'p.translationParent',
            'expr'   => 'isNull',
        ];

        return parent::getEntitiesAction($request, $userHelper);
    }
}
