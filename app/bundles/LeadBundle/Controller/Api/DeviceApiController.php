<?php

namespace MailVotech\LeadBundle\Controller\Api;

use Doctrine\Persistence\ManagerRegistry;
use MailVotech\ApiBundle\Controller\CommonApiController;
use MailVotech\ApiBundle\Helper\EntityResultHelper;
use MailVotech\CoreBundle\Entity\FormEntity;
use MailVotech\CoreBundle\Factory\ModelFactory;
use MailVotech\CoreBundle\Helper\AppVersion;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\CoreBundle\Translation\Translator;
use MailVotech\LeadBundle\Controller\LeadAccessTrait;
use MailVotech\LeadBundle\Entity\LeadDevice;
use MailVotech\LeadBundle\Model\DeviceModel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * @extends CommonApiController<LeadDevice>
 */
final class DeviceApiController extends CommonApiController
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
        DeviceModel $leadDeviceModel,
    ) {
        $this->model           = $leadDeviceModel;
        $this->entityClass     = LeadDevice::class;
        $this->entityNameOne   = 'device';
        $this->entityNameMulti = 'devices';

        parent::__construct($security, $translator, $entityResultHelper, $router, $formFactory, $appVersion, $requestStack, $doctrine, $modelFactory, $dispatcher, $coreParametersHelper);
    }

    /**
     * @param LeadDevice           $entity
     * @param FormInterface<mixed> $form
     * @param array<mixed>         $parameters
     * @param string               $action
     */
    protected function preSaveEntity(&$entity, $form, $parameters, $action = 'edit')
    {
        $lead = null;
        if (!empty($parameters['lead'])) {
            $lead = $parameters['lead'];
        } elseif (!empty($parameters['contact'])) {
            $lead = $parameters['contact'];
        }
        if ($lead) {
            $lead = $this->checkLeadAccess($lead, $action);

            if ($lead instanceof Response) {
                return $lead;
            }

            $entity->setLead($lead);
            unset($parameters['lead'], $parameters['contact']);
        } elseif ('new' === $action) {
            return $this->returnError('contact ID is mandatory', Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @param LeadDevice|FormEntity $entity
     */
    protected function checkEntityAccess($entity, $action = 'view')
    {
        return parent::checkEntityAccess($entity->getLead(), $action);
    }
}
