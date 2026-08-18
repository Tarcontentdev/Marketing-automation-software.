<?php

namespace MailVotech\ApiBundle\Model;

use MailVotech\ApiBundle\ApiEvents;
use MailVotech\ApiBundle\Entity\oAuth2\Client;
use MailVotech\ApiBundle\Entity\oAuth2\ClientRepository;
use MailVotech\ApiBundle\Event\ClientEvent;
use MailVotech\ApiBundle\Form\Type\ClientType;
use MailVotech\CoreBundle\Model\FormModel;
use MailVotech\CoreBundle\Model\GlobalSearchInterface;
use MailVotech\UserBundle\Entity\User;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @extends FormModel<Client>
 */
final class ClientModel extends FormModel implements GlobalSearchInterface
{
    /**
     * @var string
     */
    public const API_MODE_OAUTH2 = 'oauth2';

    private ?string $apiMode = null;

    private const DEFAULT_API_MODE = 'oauth2';

    private RequestStack $requestStack;

    private ClientRepository $clientRepository;

    #[Required]
    public function autowireClientModel(
        RequestStack $requestStack,
        ClientRepository $clientRepository,
    ): void {
        $this->requestStack     = $requestStack;
        $this->clientRepository = $clientRepository;
    }

    private function getApiMode(): string
    {
        if (null !== $this->apiMode) {
            return $this->apiMode;
        }

        if (null !== $request = $this->requestStack->getCurrentRequest()) {
            return $request->get('api_mode', $request->getSession()->get('mailvotech.client.filter.api_mode', self::DEFAULT_API_MODE));
        }

        return self::DEFAULT_API_MODE;
    }

    public function setApiMode(?string $apiMode): void
    {
        $this->apiMode = $apiMode;
    }

    public function getRepository(): ClientRepository
    {
        return $this->clientRepository;
    }

    public function getPermissionBase(): string
    {
        return 'api:clients';
    }

    /**
     * @throws MethodNotAllowedHttpException
     */
    public function createForm($entity, FormFactoryInterface $formFactory, $action = null, $options = []): FormInterface
    {
        if (!$entity instanceof Client) {
            throw new MethodNotAllowedHttpException(['Client']);
        }

        $params = (!empty($action)) ? ['action' => $action] : [];

        return $formFactory->create(ClientType::class, $entity, $params);
    }

    public function getEntity($id = null): ?Client
    {
        if (null === $id) {
            return 'oauth2' === $this->getApiMode() ? new Client() : null;
        }

        return parent::getEntity($id);
    }

    /**
     * @throws MethodNotAllowedHttpException
     */
    protected function dispatchEvent($action, &$entity, $isNew = false, ?Event $event = null): ?Event
    {
        if (!$entity instanceof Client) {
            throw new MethodNotAllowedHttpException(['Client']);
        }

        switch ($action) {
            case 'post_save':
                $name = ApiEvents::CLIENT_POST_SAVE;
                break;
            case 'post_delete':
                $name = ApiEvents::CLIENT_POST_DELETE;
                break;
            default:
                return null;
        }

        if ($this->dispatcher->hasListeners($name)) {
            if (!$event instanceof Event) {
                $event = new ClientEvent($entity, $isNew);
                $event->setEntityManager($this->em);
            }
            $this->dispatcher->dispatch($event, $name);

            return $event;
        }

        return null;
    }

    /**
     * @return array
     */
    public function getUserClients(User $user)
    {
        return $this->clientRepository->getUserClients($user);
    }

    /**
     * @throws MethodNotAllowedHttpException
     */
    public function revokeAccess($entity): void
    {
        if (!$entity instanceof Client) {
            throw new MethodNotAllowedHttpException(['Client']);
        }

        // remove the user from the client
        $entity->removeUser($this->userHelper->getUser());
        $this->saveEntity($entity);
    }
}
