<?php

declare(strict_types=1);

namespace MailVotech\FormBundle\Entity;

use MailVotech\CoreBundle\Entity\CommonRepository;
use MailVotech\LeadBundle\Entity\LeadField;

/**
 * @extends CommonRepository<Field>
 */
class FieldRepository extends CommonRepository
{
    public function fieldExistsByFormAndType(int $formId, string $type): bool
    {
        return (bool) $this->getEntityManager()->getConnection()->createQueryBuilder()
            ->select('1')
            ->from(MAILVOTECH_TABLE_PREFIX.Field::TABLE_NAME, 'f')
            ->where('f.type = :type')
            ->andWhere('f.form_id = :formId')
            ->setParameter('type', $type)
            ->setParameter('formId', $formId)
            ->executeQuery()
            ->fetchOne();
    }

    /**
     * @return Field[]
     */
    public function getFormFieldsMappedToLeadField(LeadField $customField): array
    {
        $qb = $this->createQueryBuilder('f')
            ->select('f')
            ->where('f.mappedField = :customField')
            ->setParameter('customField', $customField->getAlias());

        return $qb->getQuery()->getResult();
    }
}
