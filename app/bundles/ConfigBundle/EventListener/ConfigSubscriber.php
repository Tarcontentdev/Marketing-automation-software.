<?php

namespace MailVotech\ConfigBundle\EventListener;

use MailVotech\ConfigBundle\ConfigEvents;
use MailVotech\ConfigBundle\Event\ConfigEvent;
use MailVotech\ConfigBundle\Service\ConfigChangeLogger;
use MailVotech\CoreBundle\Entity\AuditLogRepository;
use MailVotech\CoreBundle\Entity\IpAddressRepository;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class ConfigSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ConfigChangeLogger $configChangeLogger,
        private IpAddressRepository $ipAddressRepository,
        private CoreParametersHelper $coreParametersHelper,
        private AuditLogRepository $auditLogRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConfigEvents::CONFIG_POST_SAVE => ['onConfigPostSave', 0],
        ];
    }

    public function onConfigPostSave(ConfigEvent $event): void
    {
        if ($originalNormData = $event->getOriginalNormData()) {
            $normData = $event->getNormData();
            // We have something to log
            $this->configChangeLogger
                ->setOriginalNormData($originalNormData)
                ->log($normData);

            if (!isset($originalNormData['trackingconfig']) && !isset($normData['trackingconfig'])) {
                return;
            }

            $oldAnonymizeIp = $originalNormData['trackingconfig']['parameters']['anonymize_ip'];
            $newAnonymizeIp = $normData['trackingconfig']['anonymize_ip'];

            if ($oldAnonymizeIp !== $newAnonymizeIp && $newAnonymizeIp && !$this->coreParametersHelper->get('anonymize_ip_address_in_background', false)) {
                $this->ipAddressRepository->anonymizeAllIpAddress();
                $this->auditLogRepository->anonymizeAllIpAddress();
            }
        }
    }
}
