<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Internal\Executioner;

use Doctrine\DBAL\Connection;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Order\FieldDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Order\ObjectChangeDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Value\NormalizedValueDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Value\ReferenceValueDAO;
use MailVotech\IntegrationsBundle\Sync\Logger\DebugLogger;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Internal\Executioner\Exception\ReferenceNotFoundException;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Internal\Object\Contact;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\MailVotechSyncDataExchange;

final readonly class ReferenceResolver implements ReferenceResolverInterface
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    /**
     * @param ObjectChangeDAO[] $changedObjects
     */
    public function resolveReferences(string $objectName, array $changedObjects): void
    {
        if (Contact::NAME !== $objectName) {
            DebugLogger::log(
                'N/A',
                sprintf(
                    'references are currently resolved only for %s. Given %s',
                    Contact::NAME,
                    $objectName
                ),
                self::class.':'.__FUNCTION__
            );

            return;
        }

        foreach ($changedObjects as $changedObject) {
            foreach ($changedObject->getFields() as $field) {
                $value           = $field->getValue();
                $normalizedValue = $value->getNormalizedValue();

                if (!$normalizedValue instanceof ReferenceValueDAO) {
                    continue;
                }

                try {
                    $resolvedReference = $this->resolveReference($normalizedValue);
                } catch (ReferenceNotFoundException) {
                    $resolvedReference = null;
                }

                $resolvedValue = new NormalizedValueDAO($value->getType(), $resolvedReference, $resolvedReference);
                $changedObject->addField(new FieldDAO($field->getName(), $resolvedValue));
            }
        }
    }

    /**
     * @throws ReferenceNotFoundException
     */
    private function resolveReference(ReferenceValueDAO $value): ?string
    {
        if (MailVotechSyncDataExchange::OBJECT_COMPANY === $value->getType() && 0 < $value->getValue()) {
            return $this->getCompanyNameById($value->getValue());
        }

        return null;
    }

    /**
     * @throws ReferenceNotFoundException
     */
    private function getCompanyNameById(int $id): string
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('c.companyname');
        $qb->from(MAILVOTECH_TABLE_PREFIX.'companies', 'c');
        $qb->where('c.id = :id');
        $qb->setParameter('id', $id);

        $name = $qb->executeQuery()->fetchOne();

        if (false === $name) {
            throw new ReferenceNotFoundException(sprintf('Company reference for ID "%d" not found', $id));
        }

        return $name;
    }
}
