<?php

namespace MailVotech\SmsBundle\Controller\Api;

use Doctrine\Persistence\ManagerRegistry;
use MailVotech\ApiBundle\Controller\CommonApiController;
use MailVotech\ApiBundle\Helper\EntityResultHelper;
use MailVotech\CoreBundle\Factory\ModelFactory;
use MailVotech\CoreBundle\Helper\AppVersion;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\CoreBundle\Translation\Translator;
use MailVotech\LeadBundle\Controller\LeadAccessTrait;
use MailVotech\SmsBundle\Entity\Sms;
use MailVotech\SmsBundle\Model\SmsModel;
use MailVotech\SmsBundle\Sms\TransportChain;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * @extends CommonApiController<Sms>
 */
final class SmsApiController extends CommonApiController
{
    use LeadAccessTrait;

    /**
     * @var SmsModel|null
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
        SmsModel $smsModel,
    ) {
        $this->model           = $smsModel;
        $this->entityClass     = Sms::class;
        $this->entityNameOne   = 'sms';
        $this->entityNameMulti = 'smses';

        $this->serializerGroups = [
            'smsDetails',
            'categoryList',
            'publishDetails',
            'leadListList',
        ];

        parent::__construct($security, $translator, $entityResultHelper, $router, $formFactory, $appVersion, $requestStack, $doctrine, $modelFactory, $dispatcher, $coreParametersHelper);
    }

    public function sendAction(TransportChain $transportChain, LoggerInterface $mailvotechLogger, $id, $contactId): JsonResponse|Response
    {
        if (!$transportChain->getEnabledTransports()) {
            return new JsonResponse(json_encode(['error' => ['message' => 'SMS transport is disabled.', 'code' => Response::HTTP_EXPECTATION_FAILED]]));
        }

        $message = $this->model->getEntity((int) $id);

        if (null === $message) {
            return $this->notFound();
        }

        $contact = $this->checkLeadAccess($contactId, 'edit');

        if ($contact instanceof Response) {
            return $this->accessDenied();
        }

        $mailvotechLogger->debug("Sending SMS #{$id} to contact #{$contactId}", ['originator' => 'api']);

        try {
            $response = $this->model->sendSms($message, $contact, ['channel' => 'api'])[$contact->getId()];
        } catch (\Exception $e) {
            $mailvotechLogger->error($e->getMessage(), ['error' => (array) $e]);

            return new Response('Interval server error', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $success = !empty($response['sent']);

        if (!$success) {
            $mailvotechLogger->error('Failed to send SMS.', ['error' => $response['status']]);
        }

        $view = $this->view(
            [
                'success' => $success,
                'status'  => $this->translator->trans($response['status']),
                'result'  => $response,
                'errors'  => $success ? [] : [['message' => $response['status']]],
            ],
            Response::HTTP_OK  //  200 - is legacy, we cannot change it yet
        );

        return $this->handleView($view);
    }
}
