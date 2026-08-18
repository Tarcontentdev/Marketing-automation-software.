<?php

namespace MailVotech\ChannelBundle\Controller\Api;

use Doctrine\Persistence\ManagerRegistry;
use MailVotech\ApiBundle\Controller\CommonApiController;
use MailVotech\ApiBundle\Helper\EntityResultHelper;
use MailVotech\ChannelBundle\ChannelEvents;
use MailVotech\ChannelBundle\Entity\Message;
use MailVotech\ChannelBundle\Event\ChannelEvent;
use MailVotech\ChannelBundle\Model\MessageModel;
use MailVotech\CoreBundle\Factory\ModelFactory;
use MailVotech\CoreBundle\Helper\AppVersion;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\CoreBundle\Translation\Translator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

/**
 * @extends CommonApiController<Message>
 */
final class MessageApiController extends CommonApiController
{
    /**
     * @var MessageModel|null
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
        MessageModel $messageModel,
    ) {
        $this->model            = $messageModel;
        $this->entityClass      = Message::class;
        $this->entityNameOne    = 'message';
        $this->entityNameMulti  = 'messages';
        $this->serializerGroups = ['messageDetails', 'messageChannelList', 'categoryList', 'publishDetails'];

        parent::__construct($security, $translator, $entityResultHelper, $router, $formFactory, $appVersion, $requestStack, $doctrine, $modelFactory, $dispatcher, $coreParametersHelper);
    }

    protected function prepareParametersFromRequest(FormInterface $form, array &$params, ?object $entity = null, array $masks = [], array $fields = []): void
    {
        parent::prepareParametersFromRequest($form, $params, $entity, $masks);

        if ('PATCH' === $this->requestStack->getCurrentRequest()->getMethod() && !isset($params['channels'])) {
            return;
        }
        if (!isset($params['channels'])) {
            $params['channels'] = [];
        }

        $channels = $this->model->getChannels();

        foreach ($channels as $channelType => $channel) {
            if (!isset($params['channels'][$channelType])) {
                $params['channels'][$channelType] = ['isEnabled' => 0];
            } else {
                $params['channels'][$channelType]['isEnabled'] = (int) $params['channels'][$channelType]['isEnabled'];
            }
            $params['channels'][$channelType]['channel'] = $channelType;
        }
    }

    /**
     * Load and set channel names to the response.
     */
    protected function preSerializeEntity(object $entity, string $action = 'view'): void
    {
        $event = $this->dispatcher->dispatch(new ChannelEvent(), ChannelEvents::ADD_CHANNEL);

        foreach ($entity->getChannels() as $channel) {
            $repository = $event->getRepositoryName($channel->getChannel());
            $nameColumn = $event->getNameColumn($channel->getChannel());
            $name       = $this->model->getChannelName($channel->getChannelId(), $repository, $nameColumn);
            $channel->setChannelName($name);
        }
    }
}
