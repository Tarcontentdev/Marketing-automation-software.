<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Tests\Functional\Sync\Notification;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\IntegrationsBundle\Helper\SyncIntegrationsHelper;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Order\NotificationDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Order\ObjectChangeDAO;
use MailVotech\IntegrationsBundle\Sync\Notification\Notifier;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Internal\Object\Contact;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\MailVotechSyncDataExchange;
use MailVotech\IntegrationsBundle\Tests\Functional\Services\SyncService\TestExamples\Integration\ExampleIntegration;
use MailVotech\IntegrationsBundle\Tests\Functional\Services\SyncService\TestExamples\Sync\SyncDataExchange\ExampleSyncDataExchange;
use MailVotech\LeadBundle\DataFixtures\ORM\LoadLeadData;
use MailVotech\LeadBundle\Entity\Lead;

final class NotifierTest extends MailVotechMysqlTestCase
{
    public function testNotifications(): void
    {
        $this->installDatabaseFixtures([LoadLeadData::class]);

        $leadRepository = $this->em->getRepository(Lead::class);
        /** @var Lead[] $leads */
        $leads = $leadRepository->findBy([], [], 2);

        /** @var SyncIntegrationsHelper $syncIntegrationsHelper */
        $syncIntegrationsHelper = self::getContainer()->get(SyncIntegrationsHelper::class);
        $syncIntegrationsHelper->addIntegration(new ExampleIntegration(new ExampleSyncDataExchange()));

        /** @var Notifier $notifier */
        $notifier = self::getContainer()->get(Notifier::class);

        $contactNotification = new NotificationDAO(
            new ObjectChangeDAO(
                ExampleIntegration::NAME,
                'Foo',
                1,
                Contact::NAME,
                (int) $leads[0]->getId()
            ),
            'This is the message'
        );
        $companyNotification = new NotificationDAO(
            new ObjectChangeDAO(
                ExampleIntegration::NAME,
                'Bar',
                2,
                MailVotechSyncDataExchange::OBJECT_COMPANY,
                (int) $leads[1]->getId()
            ),
            'This is the message'
        );

        $notifier->noteMailVotechSyncIssue([$contactNotification, $companyNotification]);
        $notifier->finalizeNotifications();

        // Check audit log
        $qb = $this->connection->createQueryBuilder();
        $qb->select('1')
            ->from(MAILVOTECH_TABLE_PREFIX.'audit_log')
            ->where(
                $qb->expr()->eq('bundle', $qb->expr()->literal(ExampleIntegration::NAME))
            );

        $this->assertCount(2, $qb->executeQuery()->fetchAllAssociative());

        // Contact event log
        $qb = $this->connection->createQueryBuilder();
        $qb->select('1')
            ->from(MAILVOTECH_TABLE_PREFIX.'lead_event_log')
            ->where(
                $qb->expr()->and(
                    $qb->expr()->eq('bundle', $qb->expr()->literal('integrations')),
                    $qb->expr()->eq('object', $qb->expr()->literal(ExampleIntegration::NAME))
                )
            );
        $this->assertCount(1, $qb->executeQuery()->fetchAllAssociative());

        // User notifications
        $qb = $this->connection->createQueryBuilder();
        $qb->select('1')
            ->from(MAILVOTECH_TABLE_PREFIX.'notifications')
            ->where(
                $qb->expr()->eq('icon_class', $qb->expr()->literal('ri-refresh-line'))
            );
        $this->assertCount(2, $qb->executeQuery()->fetchAllAssociative());
    }
}
