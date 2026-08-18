<?php

namespace MailVotech\WebhookBundle\Controller\Api;

use Doctrine\Persistence\ManagerRegistry;
use MailVotech\ApiBundle\Controller\CommonApiController;
use MailVotech\ApiBundle\Helper\EntityResultHelper;
use MailVotech\CoreBundle\Factory\ModelFactory;
use MailVotech\CoreBundle\Helper\AppVersion;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\CoreBundle\Translation\Translator;
use MailVotech\WebhookBundle\Entity\Webhook;
use MailVotech\WebhookBundle\Model\WebhookModel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * @extends CommonApiController<Webhook>
 */
final class WebhookApiController extends CommonApiController
{
    /**
     * @var WebhookModel|null
     */
    protected $model;

    public function __construct(
        CorePermissions $security,
        Translator $translator,
        EntityResultHelper $entityResultHelper,
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        AppVersion $appVersion,
        private readonly RequestStack $requestStack,
        ManagerRegistry $doctrine,
        ModelFactory $modelFactory,
        EventDispatcherInterface $dispatcher,
        CoreParametersHelper $coreParametersHelper,
        WebhookModel $webhookModel,
    ) {
        $this->model            = $webhookModel;
        $this->entityClass      = Webhook::class;
        $this->entityNameOne    = 'hook';
        $this->entityNameMulti  = 'hooks';
        $this->serializerGroups = ['hookDetails', 'categoryList', 'publishDetails'];

        parent::__construct($security, $translator, $entityResultHelper, $router, $formFactory, $appVersion, $requestStack, $doctrine, $modelFactory, $dispatcher, $coreParametersHelper);
    }

    /**
     * Gives child controllers opportunity to analyze and do whatever to an entity before going through serializer.
     */
    protected function preSerializeEntity(object $entity, string $action = 'view'): void
    {
        // We have to use this hack to have a simple array instead of the one the serializer gives us
        $entity->buildTriggers();
    }

    /**
     * @param Webhook              $entity
     * @param FormInterface<mixed> $form
     * @param array<mixed>         $parameters
     * @param string               $action
     */
    protected function preSaveEntity(&$entity, $form, $parameters, $action = 'edit'): void
    {
        $eventsToKeep = [];

        // Build webhook events from the triggers
        if (isset($parameters['triggers']) && is_array($parameters['triggers'])) {
            $entity->setTriggers($parameters['triggers']);
            $eventsToKeep = $parameters['triggers'];
        }

        // Remove events missing in the PUT request
        if ('PUT' === $this->requestStack->getCurrentRequest()->getMethod()) {
            foreach ($entity->getEvents() as $event) {
                if (!in_array($event->getEventType(), $eventsToKeep)) {
                    $entity->removeEvent($event);
                }
            }
        }
    }

    public function getTriggersAction(): Response
    {
        return $this->handleView(
            $this->view(
                [
                    'triggers' => $this->model->getEvents(),
                ]
            )
        );
    }
}
