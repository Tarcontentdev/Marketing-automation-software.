<?php

declare(strict_types=1);

namespace MailVotech\PointBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use MailVotech\CoreBundle\Event\EntityExportEvent;
use MailVotech\CoreBundle\Event\EntityImportAnalyzeEvent;
use MailVotech\CoreBundle\Event\EntityImportEvent;
use MailVotech\CoreBundle\Event\EntityImportUndoEvent;
use MailVotech\CoreBundle\EventListener\ImportExportTrait;
use MailVotech\CoreBundle\Helper\IpLookupHelper;
use MailVotech\CoreBundle\Model\AuditLogModel;
use MailVotech\PointBundle\Entity\Group;
use MailVotech\PointBundle\Entity\GroupRepository;
use MailVotech\PointBundle\Model\PointGroupModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class GroupImportExportSubscriber implements EventSubscriberInterface
{
    use ImportExportTrait;

    public function __construct(
        private PointGroupModel $pointGroupModel,
        private EntityManagerInterface $entityManager,
        private GroupRepository $groupRepository,
        private AuditLogModel $auditLogModel,
        private IpLookupHelper $ipLookupHelper,
        private DenormalizerInterface $serializer,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntityExportEvent::class        => ['onPointGroupExport', 0],
            EntityImportEvent::class        => ['onPointGroupImport', 0],
            EntityImportUndoEvent::class    => ['onUndoImport', 0],
            EntityImportAnalyzeEvent::class => ['onDuplicationCheck', 0],
        ];
    }

    public function onPointGroupExport(EntityExportEvent $event): void
    {
        if (Group::ENTITY_NAME !== $event->getEntityName()) {
            return;
        }

        $pointGroupId = $event->getEntityId();
        $pointGroup   = $this->pointGroupModel->getEntity($pointGroupId);
        if (!$pointGroup) {
            return;
        }

        $pointGroupData = [
            'id'          => $pointGroup->getId(),
            'name'        => $pointGroup->getName(),
            'description' => $pointGroup->getDescription(),
            'is_published'=> $pointGroup->isPublished(),
            'uuid'        => $pointGroup->getUuid(),
        ];

        $event->addEntity(Group::ENTITY_NAME, $pointGroupData);
        $this->logAction('export', $pointGroup->getId(), $pointGroupData);
    }

    public function onPointGroupImport(EntityImportEvent $event): void
    {
        if (Group::ENTITY_NAME !== $event->getEntityName() || !$event->getEntityData()) {
            return;
        }

        $stats = [
            EntityImportEvent::NEW    => ['names' => [], 'ids' => [], 'count' => 0],
            EntityImportEvent::UPDATE => ['names' => [], 'ids' => [], 'count' => 0],
        ];

        foreach ($event->getEntityData() as $element) {
            $group = $this->groupRepository->findOneBy(['uuid' => $element['uuid']]);
            $isNew = !$group;

            $group ??= new Group();
            $this->serializer->denormalize(
                $element,
                Group::class,
                null,
                ['object_to_populate' => $group]
            );
            $this->pointGroupModel->saveEntity($group);

            $event->addEntityIdMap((int) $element['id'], $group->getId());

            $status                    = $isNew ? EntityImportEvent::NEW : EntityImportEvent::UPDATE;
            $stats[$status]['names'][] = $group->getName();
            $stats[$status]['ids'][]   = $group->getId();
            ++$stats[$status]['count'];

            $this->logAction('import', $group->getId(), $element);
        }

        foreach ($stats as $status => $info) {
            if ($info['count'] > 0) {
                $event->setStatus($status, [Group::ENTITY_NAME => $info]);
            }
        }
    }

    public function onUndoImport(EntityImportUndoEvent $event): void
    {
        if (Group::ENTITY_NAME !== $event->getEntityName()) {
            return;
        }

        $summary  = $event->getSummary();

        if (!isset($summary['ids']) || empty($summary['ids'])) {
            return;
        }
        foreach ($summary['ids'] as $id) {
            $entity = $this->groupRepository->find($id);

            if ($entity) {
                $this->entityManager->remove($entity);
                $this->logAction('undo_import', $id, ['deletedEntity' => Group::class]);
            }
        }

        $this->entityManager->flush();
    }

    public function onDuplicationCheck(EntityImportAnalyzeEvent $event): void
    {
        $this->performDuplicationCheck(
            $event,
            Group::ENTITY_NAME,
            Group::class,
            'name',
            $this->entityManager
        );
    }

    /**
     * @param array<string, mixed> $details
     */
    private function logAction(string $action, int $objectId, array $details): void
    {
        $this->auditLogModel->writeToLog([
            'bundle'    => 'point',
            'object'    => 'pointGroup',
            'objectId'  => $objectId,
            'action'    => $action,
            'details'   => $details,
            'ipAddress' => $this->ipLookupHelper->getIpAddressFromRequest(),
        ]);
    }
}
