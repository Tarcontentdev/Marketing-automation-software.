<?php

namespace MailVotech\PointBundle\Controller\Api;

use Doctrine\Persistence\ManagerRegistry;
use MailVotech\ApiBundle\Controller\CommonApiController;
use MailVotech\ApiBundle\Helper\EntityResultHelper;
use MailVotech\CoreBundle\Factory\ModelFactory;
use MailVotech\CoreBundle\Helper\AppVersion;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Helper\InputHelper;
use MailVotech\CoreBundle\Helper\IpLookupHelper;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\CoreBundle\Translation\Translator;
use MailVotech\LeadBundle\Controller\LeadAccessTrait;
use MailVotech\LeadBundle\Model\LeadModel;
use MailVotech\PointBundle\Entity\Point;
use MailVotech\PointBundle\Model\PointModel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * @extends CommonApiController<Point>
 */
final class PointApiController extends CommonApiController
{
    use LeadAccessTrait;

    /**
     * @var PointModel|null
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
        private LeadModel $leadModel,
        PointModel $pointModel,
    ) {
        $this->model            = $pointModel;
        $this->entityClass      = Point::class;
        $this->entityNameOne    = 'point';
        $this->entityNameMulti  = 'points';
        $this->serializerGroups = ['pointDetails', 'categoryList', 'publishDetails'];

        parent::__construct($security, $translator, $entityResultHelper, $router, $formFactory, $appVersion, $requestStack, $doctrine, $modelFactory, $dispatcher, $coreParametersHelper);
    }

    /**
     * Return array of available point action types.
     */
    public function getPointActionTypesAction(): Response
    {
        if (!$this->security->isGranted([$this->permissionBase.':view', $this->permissionBase.':viewown'])) {
            return $this->accessDenied();
        }

        $actionTypes = $this->model->getPointActions();
        $view        = $this->view(['pointActionTypes' => $actionTypes['list']]);

        return $this->handleView($view);
    }

    /**
     * Subtract points from a lead.
     *
     * @param int    $leadId
     * @param string $operator
     * @param int    $delta
     */
    public function adjustPointsAction(Request $request, IpLookupHelper $ipLookupHelper, $leadId, $operator, $delta): Response
    {
        $lead = $this->checkLeadAccess($leadId, 'edit');
        if ($lead instanceof Response) {
            return $lead;
        }

        try {
            $this->logApiPointChange($request, $ipLookupHelper, $lead, $delta, $operator);
        } catch (\Exception $e) {
            return $this->returnError($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return $this->handleView($this->view(['success' => 1], Response::HTTP_OK));
    }

    /**
     * Log the lead points change.
     *
     * @param int $delta
     */
    protected function logApiPointChange(Request $request, IpLookupHelper $ipLookupHelper, $lead, $delta, $operator): void
    {
        $ip         = $ipLookupHelper->getIpAddress();
        $eventName  = InputHelper::clean($request->request->get('eventName', $this->translator->trans('mailvotech.lead.lead.submitaction.operator_'.$operator)));
        $actionName = InputHelper::clean($request->request->get('actionName', $this->translator->trans('mailvotech.lead.event.api')));

        $lead->adjustPoints($delta, $operator);
        $lead->addPointsChangeLogEntry('API', $eventName, $actionName, $delta, $ip);
        $this->leadModel->saveEntity($lead, false);
    }
}
