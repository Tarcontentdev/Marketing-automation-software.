<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Tests\Functional\EventListener;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\IntegrationsBundle\Entity\FieldChangeRepository;
use MailVotech\IntegrationsBundle\Helper\SyncIntegrationsHelper;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Event\LeadEvent;
use MailVotech\LeadBundle\LeadEvents;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class LeadSubscriberTest extends MailVotechMysqlTestCase
{
    private EventDispatcherInterface $dispatcher;

    private FieldChangeRepository $fieldChangeRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dispatcher            = self::getContainer()->get(EventDispatcherInterface::class);
        $this->fieldChangeRepository = self::getContainer()->get(FieldChangeRepository::class);

        self::getContainer()->set(
            SyncIntegrationsHelper::class,
            new class() extends SyncIntegrationsHelper {
                public function __construct()
                {
                }

                public function hasObjectSyncEnabled(string $object): bool
                {
                    return true;
                }

                /**
                 * @return array<int,string>
                 */
                public function getEnabledIntegrations(): array
                {
                    return ['unicorn'];
                }
            }
        );
    }

    public function testContactPostSaveWithProxy(): void
    {
        // The contact must exist in the database in order to create a reference later.
        $contactReal = new Lead();
        $this->em->persist($contactReal);
        $this->em->flush();
        $this->em->clear();

        // By getting a reference we'll get a proxy class instead of the real entity class.
        /** @var Lead $contactProxy */
        $contactProxy = $this->em->getReference(Lead::class, $contactReal->getId());
        $contactProxy->__set('email', 'john@doe.email');
        $contactProxy->setPoints(100);
        $event = new LeadEvent($contactProxy, true);

        $this->dispatcher->dispatch($event, LeadEvents::LEAD_POST_SAVE);

        $fieldChanges = $this->fieldChangeRepository->findChangesForObject('unicorn', Lead::class, $contactReal->getId());
        $this->assertCount(2, $fieldChanges, print_r($fieldChanges, true));

        $this->assertSame('unicorn', $fieldChanges[0]['integration']);
        $this->assertSame($contactReal->getId(), (int) $fieldChanges[0]['object_id']);
        $this->assertSame(Lead::class, $fieldChanges[0]['object_type']);
        $this->assertSame('email', $fieldChanges[0]['column_name']);
        $this->assertSame('string', $fieldChanges[0]['column_type']);
        $this->assertSame('john@doe.email', $fieldChanges[0]['column_value']);

        $this->assertSame('unicorn', $fieldChanges[1]['integration']);
        $this->assertSame($contactReal->getId(), (int) $fieldChanges[1]['object_id']);
        $this->assertSame(Lead::class, $fieldChanges[1]['object_type']);
        $this->assertSame('points', $fieldChanges[1]['column_name']);
        $this->assertSame('int', $fieldChanges[1]['column_type']);
        $this->assertSame('100', $fieldChanges[1]['column_value']);
    }
}
