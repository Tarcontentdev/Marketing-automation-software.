<?php

namespace MailVotech\CampaignBundle\Controller\Api;

use Doctrine\Persistence\ManagerRegistry;
use MailVotech\ApiBundle\Controller\CommonApiController;
use MailVotech\ApiBundle\Helper\EntityResultHelper;
use MailVotech\ApiBundle\Serializer\Exclusion\FieldExclusionStrategy;
use MailVotech\CampaignBundle\Entity\Event;
use MailVotech\CampaignBundle\Model\EventModel;
use MailVotech\CoreBundle\Entity\FormEntity;
use MailVotech\CoreBundle\Factory\ModelFactory;
use MailVotech\CoreBundle\Helper\AppVersion;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\CoreBundle\Translation\Translator;
use MailVotech\LeadBundle\Controller\LeadAccessTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

/**
 * @extends CommonApiController<Event>
 */
final class EventApiController extends CommonApiController
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
        EventModel $campaignEventModel,
    ) {
        $this->model                    = $campaignEventModel;
        $this->entityClass              = Event::class;
        $this->entityNameOne            = 'event';
        $this->entityNameMulti          = 'events';
        $this->serializerGroups         = ['campaignEventStandaloneDetails', 'campaignList'];
        $this->parentChildrenLevelDepth = 1;

        // Don't include campaign in children/parent arrays
        $this->addExclusionStrategy(new FieldExclusionStrategy(['campaign'], 1));

        parent::__construct($security, $translator, $entityResultHelper, $router, $formFactory, $appVersion, $requestStack, $doctrine, $modelFactory, $dispatcher, $coreParametersHelper);
    }

    /**
     * @param Event|FormEntity $entity
     */
    protected function checkEntityAccess($entity, $action = 'view')
    {
        // Use the campaign for permission checks
        return parent::checkEntityAccess($entity->getCampaign(), $action);
    }
}
