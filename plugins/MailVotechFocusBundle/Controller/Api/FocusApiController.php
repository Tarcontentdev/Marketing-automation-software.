<?php

namespace MailVotechPlugin\MailVotechFocusBundle\Controller\Api;

use Doctrine\Persistence\ManagerRegistry;
use MailVotech\ApiBundle\Controller\CommonApiController;
use MailVotech\ApiBundle\Helper\EntityResultHelper;
use MailVotech\CoreBundle\Factory\ModelFactory;
use MailVotech\CoreBundle\Helper\AppVersion;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\CoreBundle\Translation\Translator;
use MailVotechPlugin\MailVotechFocusBundle\Entity\Focus;
use MailVotechPlugin\MailVotechFocusBundle\Model\FocusModel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * @extends CommonApiController<Focus>
 */
final class FocusApiController extends CommonApiController
{
    /**
     * @var FocusModel|null
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
        FocusModel $focusModel,
    ) {
        $this->model           = $focusModel;
        $this->entityClass     = Focus::class;
        $this->entityNameOne   = 'focus';
        $this->entityNameMulti = 'focus';
        $this->permissionBase  = 'focus:items';
        $this->dataInputMasks  = [
            'html'   => 'html',
            'editor' => 'html',
        ];
        $this->serializerGroups = [
            'focusDetails',
            'categoryList',
            'publishDetails',
        ];

        parent::__construct($security, $translator, $entityResultHelper, $router, $formFactory, $appVersion, $requestStack, $doctrine, $modelFactory, $dispatcher, $coreParametersHelper);
    }

    public function generateJsAction($id): Response
    {
        $focus = $this->model->getEntity($id);
        $view  = $this->view(['js' => $this->model->generateJavascript($focus)], Response::HTTP_OK);

        return $this->handleView($view);
    }
}
