<?php

declare(strict_types=1);

namespace MailVotech\PointBundle\Controller\Api;

use Doctrine\Persistence\ManagerRegistry;
use MailVotech\ApiBundle\Controller\CommonApiController;
use MailVotech\ApiBundle\Helper\EntityResultHelper;
use MailVotech\CoreBundle\Factory\ModelFactory;
use MailVotech\CoreBundle\Helper\AppVersion;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\CoreBundle\Translation\Translator;
use MailVotech\PointBundle\Entity\PointInsight;
use MailVotech\PointBundle\Model\InsightModel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

/**
 * @extends CommonApiController<PointInsight>
 */
final class PointInsightApiController extends CommonApiController
{
    /**
     * @var InsightModel
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
        InsightModel $insightModel,
    ) {
        $this->model            = $insightModel;
        $this->entityClass      = PointInsight::class;
        $this->entityNameOne    = 'insight';
        $this->entityNameMulti  = 'insights';
        $this->serializerGroups = ['pointInsightDetails', 'categoryList'];
        $this->permissionBase   = 'point:insights';

        parent::__construct($security, $translator, $entityResultHelper, $router, $formFactory, $appVersion, $requestStack, $doctrine, $modelFactory, $dispatcher, $coreParametersHelper);
    }
}
