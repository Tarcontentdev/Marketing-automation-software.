<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Tests\Functional\Command;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\IntegrationsBundle\Command\CleanupCommand;
use MailVotech\IntegrationsBundle\Entity\FieldChange;
use MailVotech\LeadBundle\Entity\Lead;
use Symfony\Component\Console\Command\Command;

final class CleanupCommandTest extends MailVotechMysqlTestCase
{
    public function testOrphanFieldChangeRecordDeleted(): void
    {
        $lead                    = $this->createLead();
        $fieldChangeExistLead    = $this->createFieldChange($lead->getId());
        $fieldChangeNonExistLead = $this->createFieldChange(9999);
        $response                = $this->testSymfonyCommand(CleanupCommand::NAME);
        $this->assertSame(Command::SUCCESS, $response->getStatusCode());
        $this->assertStringContainsString('1 records deleted.', $response->getDisplay());

        $fieldChangeRecordDeleted = $this->em->getRepository(FieldChange::class)->findOneBy(['id' => $fieldChangeNonExistLead->getId()]);
        $this->assertNotInstanceOf(FieldChange::class, $fieldChangeRecordDeleted);
        $fieldChangeRecordShouldNotDeleted = $this->em->getRepository(FieldChange::class)->findOneBy(['id' => $fieldChangeExistLead->getId()]);
        $this->assertInstanceOf(FieldChange::class, $fieldChangeRecordShouldNotDeleted);
    }

    public function testWhenNoRecordsToDelete(): void
    {
        $response = $this->testSymfonyCommand(CleanupCommand::NAME);
        $this->assertSame(Command::SUCCESS, $response->getStatusCode());
        $this->assertStringContainsString('0 records deleted.', $response->getDisplay());
    }

    private function createFieldChange(int $objectID): FieldChange
    {
        $fieldChange = new FieldChange();
        $fieldChange->setIntegration('testIntegration');
        $fieldChange->setObjectId($objectID);
        $fieldChange->setObjectType(Lead::class);
        $fieldChange->setModifiedAt(new \DateTime('now', new \DateTimeZone('UTC')));
        $fieldChange->setColumnName('firstname');
        $fieldChange->setColumnType('string');
        $fieldChange->setColumnValue('test-value');
        $this->em->persist($fieldChange);
        $this->em->flush();

        return $fieldChange;
    }

    private function createLead(): Lead
    {
        $lead = new Lead();
        $this->em->persist($lead);
        $this->em->flush();

        return $lead;
    }
}
