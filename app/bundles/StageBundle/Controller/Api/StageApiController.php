<?php

namespace MailVotech\StageBundle\Controller\Api;

use Doctrine\Persistence\ManagerRegistry;
use MailVotech\ApiBundle\Controller\CommonApiController;
use MailVotech\ApiBundle\Helper\EntityResultHelper;
use MailVotech\CoreBundle\Factory\ModelFactory;
use MailVotech\CoreBundle\Helper\AppVersion;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\CoreBundle\Translation\Translator;
use MailVotech\LeadBundle\Controller\LeadAccessTrait;
use MailVotech\LeadBundle\Model\LeadModel;
use MailVotech\StageBundle\Entity\Stage;
use MailVotech\StageBundle\Model\StageModel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * @extends CommonApiController<Stage>
 */
final class StageApiController extends CommonApiController
{
    use LeadAccessTrait;

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
        StageModel $stageModel,
        private LeadModel $leadModel,
    ) {
        $this->model            = $stageModel;
        $this->entityClass      = Stage::class;
        $this->entityNameOne    = 'stage';
        $this->entityNameMulti  = 'stages';
        $this->serializerGroups = ['stageDetails', 'categoryList', 'publishDetails'];

        parent::__construct($security, $translator, $entityResultHelper, $router, $formFactory, $appVersion, $requestStack, $doctrine, $modelFactory, $dispatcher, $coreParametersHelper);
    }

    /**
     * Adds a contact to a list.
     *
     * @param int $id        Stage ID
     * @param int $contactId Lead ID
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function addContactAction($id, $contactId): Response
    {
        $stage = $this->model->getEntity($id);

        if (null === $stage) {
            return $this->notFound();
        }

        $contact = $this->checkLeadAccess($contactId, 'edit');

        if ($contact instanceof Response) {
            return $contact;
        }

        if (!$this->security->isGranted('stage:stages:view')) {
            return $this->accessDenied();
        }

        $this->leadModel->addToStage(
            $contact,
            $stage,
            'API: '.$this->translator->trans('mailvotech.stage.event.added.batch')
        );
        $this->leadModel->saveEntity($contact);

        return $this->handleView($this->view(['success' => 1], Response::HTTP_OK));
    }

    /**
     * Removes given contact from a list.
     *
     * @param int $id        Stage ID
     * @param int $contactId Lead ID
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function removeContactAction($id, $contactId): Response
    {
        $stage = $this->model->getEntity($id);

        if (null === $stage) {
            return $this->notFound();
        }

        $contact = $this->checkLeadAccess($contactId, 'edit');

        if ($contact instanceof Response) {
            return $contact;
        }

        if (!$this->security->isGranted('stage:stages:view')) {
            return $this->accessDenied();
        }

        $this->leadModel->removeFromStage(
            $contact,
            $stage,
            'API: '.$this->translator->trans('mailvotech.stage.event.removed.batch')
        );

        return $this->handleView($this->view(['success' => 1], Response::HTTP_OK));
    }
}
