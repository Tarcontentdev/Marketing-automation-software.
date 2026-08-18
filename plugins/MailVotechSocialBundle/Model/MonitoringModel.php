<?php

namespace MailVotechPlugin\MailVotechSocialBundle\Model;

use MailVotech\CoreBundle\Model\FormModel;
use MailVotechPlugin\MailVotechSocialBundle\Entity\Monitoring;
use MailVotechPlugin\MailVotechSocialBundle\Entity\MonitoringRepository;
use MailVotechPlugin\MailVotechSocialBundle\Event as Events;
use MailVotechPlugin\MailVotechSocialBundle\Form\Type\MonitoringType;
use MailVotechPlugin\MailVotechSocialBundle\Form\Type\TwitterHashtagType;
use MailVotechPlugin\MailVotechSocialBundle\Form\Type\TwitterMentionType;
use MailVotechPlugin\MailVotechSocialBundle\SocialEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @extends FormModel<Monitoring>
 */
final class MonitoringModel extends FormModel
{
    private MonitoringRepository $monitoringRepository;

    #[Required]
    public function autowireMonitoringModel(
        MonitoringRepository $monitoringRepository,
    ): void {
        $this->monitoringRepository = $monitoringRepository;
    }

    /**
     * @var array<string, mixed>
     */
    private array $networkTypes = [
        'twitter_handle' => [
            'label' => 'mailvotech.social.monitoring.type.list.twitter.handle',
            'form'  => TwitterMentionType::class,
        ],
        'twitter_hashtag' => [
            'label' => 'mailvotech.social.monitoring.type.list.twitter.hashtag',
            'form'  => TwitterHashtagType::class,
        ],
    ];

    /**
     * @param object      $entity
     * @param string|null $action
     * @param mixed[]     $options
     */
    public function createForm($entity, FormFactoryInterface $formFactory, $action = null, $options = []): FormInterface
    {
        if (!$entity instanceof Monitoring) {
            throw new MethodNotAllowedHttpException(['Monitoring']);
        }

        if (!empty($action)) {
            $options['action'] = $action;
        }

        return $formFactory->create(MonitoringType::class, $entity, $options);
    }

    /**
     * Get a specific entity or generate a new one if id is empty.
     */
    public function getEntity($id = null): ?Monitoring
    {
        return $id ? parent::getEntity($id) : new Monitoring();
    }

    /**
     * @throws MethodNotAllowedHttpException
     */
    protected function dispatchEvent($action, &$entity, $isNew = false, ?Event $event = null): ?Event
    {
        if (!$entity instanceof Monitoring) {
            throw new MethodNotAllowedHttpException(['Monitoring']);
        }

        switch ($action) {
            case 'pre_save':
                $name = SocialEvents::MONITOR_PRE_SAVE;
                break;
            case 'post_save':
                $name = SocialEvents::MONITOR_POST_SAVE;
                break;
            case 'pre_delete':
                $name = SocialEvents::MONITOR_PRE_DELETE;
                break;
            case 'post_delete':
                $name = SocialEvents::MONITOR_POST_DELETE;
                break;
            default:
                return null;
        }

        if ($this->dispatcher->hasListeners($name)) {
            if (!$event instanceof Event) {
                $event = new Events\SocialEvent($entity, $isNew);
            }

            $this->dispatcher->dispatch($event, $name);

            return $event;
        }

        return null;
    }

    /**
     * @param Monitoring $monitoringEntity
     * @param bool       $unlock
     */
    public function saveEntity($monitoringEntity, $unlock = true): void
    {
        // we're editing an existing record
        if (!$monitoringEntity->isNew()) {
            // increase the revision
            $revision = $monitoringEntity->getRevision();
            ++$revision;
            $monitoringEntity->setRevision($revision);
        } // is new
        else {
            $now = new \DateTime();
            $monitoringEntity->setDateAdded($now);
        }

        parent::saveEntity($monitoringEntity, $unlock);
    }

    public function getRepository(): MonitoringRepository
    {
        return $this->monitoringRepository;
    }

    public function getPermissionBase(): string
    {
        return 'mailvotechSocial:monitoring';
    }

    /**
     * @return string[]
     */
    public function getNetworkTypes(): array
    {
        $types = [];
        foreach ($this->networkTypes as $type => $data) {
            $types[$type] = $data['label'];
        }

        return $types;
    }

    /**
     * @param string $type
     *
     * @return string|null
     */
    public function getFormByType($type)
    {
        return array_key_exists($type, $this->networkTypes) ? $this->networkTypes[$type]['form'] : null;
    }
}
