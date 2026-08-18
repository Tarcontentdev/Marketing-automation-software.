<?php

declare(strict_types=1);

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
use MailVotech\LeadBundle\Model\LeadModel;
use MailVotech\PointBundle\Entity\Group;
use MailVotech\PointBundle\Model\PointGroupModel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * @extends CommonApiController<Group>
 */
final class PointGroupsApiController extends CommonApiController
{
    /**
     * @var PointGroupModel
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
        PointGroupModel $pointGroupModel,
        private readonly LeadModel $leadModel,
    ) {
        $this->model            = $pointGroupModel;
        $this->entityClass      = Group::class;
        $this->entityNameOne    = 'pointGroup';
        $this->entityNameMulti  = 'pointGroups';
        $this->serializerGroups = ['pointGroupDetails', 'pointGroupList', 'publishDetails'];

        parent::__construct($security, $translator, $entityResultHelper, $router, $formFactory, $appVersion, $requestStack, $doctrine, $modelFactory, $dispatcher, $coreParametersHelper);
    }

    public function getContactPointGroupsAction(int $contactId): Response
    {
        $contact = $this->leadModel->getEntity($contactId);

        if (null === $contact) {
            return $this->notFound($this->translator->trans('mailvotech.lead.event.api.lead.not.found'));
        }

        if (!$this->checkEntityAccess($contact)) {
            return $this->accessDenied();
        }

        $groupScores = $contact->getGroupScores();
        $view        = $this->view(
            [
                'total'       => count($groupScores),
                'groupScores' => $groupScores,
            ],
            Response::HTTP_OK
        );

        $context = $view->getContext()->setGroups(['groupContactScoreDetails', 'pointGroupDetails']);
        $view->setContext($context);

        return $this->handleView($view);
    }

    public function getContactPointGroupAction(int $contactId, int $groupId): Response
    {
        $contact = $this->leadModel->getEntity($contactId);

        if (null === $contact) {
            return $this->notFound($this->translator->trans('mailvotech.lead.event.api.lead.not.found'));
        }

        if (!$this->checkEntityAccess($contact)) {
            return $this->accessDenied();
        }

        $pointGroup = $this->model->getEntity($groupId);
        if (null === $pointGroup) {
            return $this->notFound($this->translator->trans('mailvotech.lead.event.api.point.group.not.found'));
        }

        $groupScore  = $contact->getGroupScore($pointGroup);
        $view        = $this->view(
            [
                'groupScore' => $groupScore,
            ],
            Response::HTTP_OK
        );

        $context = $view->getContext()->setGroups(['groupContactScoreDetails', 'pointGroupDetails']);
        $view->setContext($context);

        return $this->handleView($view);
    }

    public function adjustGroupPointsAction(Request $request, IpLookupHelper $ipLookupHelper, int $contactId, int $groupId, string $operator, int $value): Response
    {
        $contact = $this->leadModel->getEntity($contactId);

        if (null === $contact) {
            return $this->notFound($this->translator->trans('mailvotech.lead.event.api.lead.not.found'));
        }

        if (!$this->checkEntityAccess($contact)) {
            return $this->accessDenied();
        }

        $pointGroup = $this->model->getEntity($groupId);
        if (null === $pointGroup) {
            return $this->notFound($this->translator->trans('mailvotech.lead.event.api.point.group.not.found'));
        }

        if (!PointGroupModel::isAllowedPointOperation($operator)) {
            return $this->badRequest($this->translator->trans('mailvotech.lead.event.api.operation.not.allowed'));
        }

        $oldScore    = $contact->getGroupScore($pointGroup)?->getScore();
        $contact     = $this->model->adjustPoints($contact, $pointGroup, $value, $operator);
        $newScore    = $contact->getGroupScore($pointGroup)->getScore();
        $delta       = $newScore - ($oldScore ?? 0);

        $eventName  = InputHelper::clean($request->request->get('eventName', $this->translator->trans('mailvotech.point.event.manual_change')));
        $actionName = InputHelper::clean($request->request->get('actionName', $this->translator->trans('mailvotech.lead.event.api')));
        $contact->addPointsChangeLogEntry(
            type: 'API',
            name: $eventName,
            action: $actionName,
            pointChanges: $delta,
            ip: $ipLookupHelper->getIpAddress(),
            group: $pointGroup
        );
        $this->leadModel->saveEntity($contact, false);

        $view    = $this->view(['groupScore' => $contact->getGroupScore($pointGroup)], Response::HTTP_OK);
        $context = $view->getContext()->setGroups(['groupContactScoreDetails', 'pointGroupDetails']);
        $view->setContext($context);

        return $this->handleView($view);
    }
}
