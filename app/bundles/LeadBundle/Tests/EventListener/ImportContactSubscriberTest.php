<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\EventListener;

use Doctrine\Common\Collections\ArrayCollection;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\CoreBundle\Translation\Translator;
use MailVotech\LeadBundle\Entity\Import;
use MailVotech\LeadBundle\Entity\LeadEventLog;
use MailVotech\LeadBundle\Entity\Tag;
use MailVotech\LeadBundle\Event\ImportInitEvent;
use MailVotech\LeadBundle\Event\ImportMappingEvent;
use MailVotech\LeadBundle\Event\ImportProcessEvent;
use MailVotech\LeadBundle\Event\ImportValidateEvent;
use MailVotech\LeadBundle\EventListener\ImportContactSubscriber;
use MailVotech\LeadBundle\Field\FieldList;
use MailVotech\LeadBundle\Model\LeadModel;
use PHPUnit\Framework\Assert;
use Symfony\Component\Form\Form;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ImportContactSubscriberTest extends \PHPUnit\Framework\TestCase
{
    public function testHandleValidateTags(): void
    {
        $tag = new Tag();
        $tag->setTag('tagLabel');

        $formMock = $this->createMock(Form::class);
        $formMock->method('getData')
            ->willReturn(
                [
                    'name' => 'Bud',
                    'tags' => new ArrayCollection([$tag]),
                ]
            );

        $event      = new ImportValidateEvent('contacts', $formMock);
        $subscriber = new ImportContactSubscriber(
            new class() extends FieldList {
                public function __construct()
                {
                }

                public function getFieldList(bool $byGroup = true, bool $alphabetical = true, array $filters = ['isPublished' => true, 'object' => 'lead']): array
                {
                    return [];
                }
            },
            $this->getCorePermissionsFake(),
            $this->getLeadModelFake(),
            $this->getTranslatorFake()
        );

        $subscriber->onValidateImport($event);

        $this->assertSame(['tagLabel'], $event->getTags());
        $this->assertSame(['name' => 'Bud'], $event->getMatchedFields());
    }

    /**
     * @see https://github.com/mailvotech/mailvotech/issues/11080
     */
    public function testHandleFieldWithIntValues(): void
    {
        $formMock = $this->createMock(Form::class);
        $formMock->method('getData')
            ->willReturn(
                [
                    'name'           => 'Bud',
                    'skip_if_exists' => 1,
                ]
            );

        $event      = new ImportValidateEvent('contacts', $formMock);
        $subscriber = new ImportContactSubscriber(
            new class() extends FieldList {
                public function __construct()
                {
                }

                public function getFieldList(bool $byGroup = true, bool $alphabetical = true, array $filters = ['isPublished' => true, 'object' => 'lead']): array
                {
                    return [];
                }
            },
            $this->getCorePermissionsFake(),
            $this->getLeadModelFake(),
            $this->getTranslatorFake()
        );

        $subscriber->onValidateImport($event);

        $this->assertSame(['name' => 'Bud'], $event->getMatchedFields());
    }

    public function testOnImportInitForUknownObject(): void
    {
        $subscriber = new ImportContactSubscriber(
            $this->getFieldListFake(),
            $this->getCorePermissionsFake(),
            $this->getLeadModelFake(),
            $this->getTranslatorFake()
        );
        $event = new ImportInitEvent('unicorn');
        $subscriber->onImportInit($event);
        $this->assertFalse($event->objectSupported);
    }

    public function testOnImportInitForContactsObjectWithoutPermissions(): void
    {
        $subscriber = new ImportContactSubscriber(
            $this->getFieldListFake(),
            new class() extends CorePermissions {
                public function __construct()
                {
                }

                /**
                 * @param string $requestedPermission
                 */
                public function isGranted($requestedPermission, $mode = 'MATCH_ALL', $userEntity = null, $allowUnknown = false): bool
                {
                    Assert::assertSame('lead:imports:create', $requestedPermission);

                    return false;
                }
            },
            $this->getLeadModelFake(),
            $this->getTranslatorFake()
        );
        $event = new ImportInitEvent('contacts');
        $this->expectException(AccessDeniedException::class);
        $subscriber->onImportInit($event);
    }

    public function testOnImportInitForContactsObjectWithPermissions(): void
    {
        $subscriber = new ImportContactSubscriber(
            $this->getFieldListFake(),
            new class() extends CorePermissions {
                public function __construct()
                {
                }

                /**
                 * @param string $requestedPermission
                 */
                public function isGranted($requestedPermission, $mode = 'MATCH_ALL', $userEntity = null, $allowUnknown = false): bool
                {
                    Assert::assertSame('lead:imports:create', $requestedPermission);

                    return true;
                }
            },
            $this->getLeadModelFake(),
            $this->getTranslatorFake()
        );
        $event = new ImportInitEvent('contacts');
        $subscriber->onImportInit($event);
        $this->assertTrue($event->objectSupported);
        $this->assertSame('lead', $event->objectSingular);
        $this->assertSame('mailvotech.lead.leads', $event->objectName);
        $this->assertSame('#mailvotech_contact_index', $event->activeLink);
        $this->assertSame('mailvotech_contact_index', $event->indexRoute);
    }

    public function testOnFieldMappingForUnknownObject(): void
    {
        $subscriber = new ImportContactSubscriber(
            $this->getFieldListFake(),
            $this->getCorePermissionsFake(),
            $this->getLeadModelFake(),
            $this->getTranslatorFake()
        );
        $event = new ImportMappingEvent('unicorn');
        $subscriber->onFieldMapping($event);
        $this->assertFalse($event->objectSupported);
    }

    public function testOnFieldMapping(): void
    {
        $subscriber = new ImportContactSubscriber(
            new class() extends FieldList {
                public function __construct()
                {
                }

                /**
                 * @param array<bool|string> $filters
                 *
                 * @return string[]
                 */
                public function getFieldList(bool $byGroup = true, bool $alphabetical = true, array $filters = ['isPublished' => true, 'object' => 'lead']): array
                {
                    return ['some fields'];
                }
            },
            $this->getCorePermissionsFake(),
            $this->getLeadModelFake(),
            $this->getTranslatorFake()
        );
        $event = new ImportMappingEvent('contacts');
        $subscriber->onFieldMapping($event);
        $this->assertTrue($event->objectSupported);
        $this->assertSame([
            'mailvotech.lead.contact' => [
                'id' => 'mailvotech.lead.import.label.id',
                'some fields',
            ],
            'mailvotech.lead.company' => [
                'some fields',
            ],
            'mailvotech.lead.special_fields' => [
                'dateAdded'      => 'mailvotech.lead.import.label.dateAdded',
                'createdByUser'  => 'mailvotech.lead.import.label.createdByUser',
                'dateModified'   => 'mailvotech.lead.import.label.dateModified',
                'modifiedByUser' => 'mailvotech.lead.import.label.modifiedByUser',
                'lastActive'     => 'mailvotech.lead.import.label.lastActive',
                'dateIdentified' => 'mailvotech.lead.import.label.dateIdentified',
                'ip'             => 'mailvotech.lead.import.label.ip',
                'stage'          => 'mailvotech.lead.import.label.stage',
                'doNotEmail'     => 'mailvotech.lead.import.label.doNotEmail',
                'ownerusername'  => 'mailvotech.lead.import.label.ownerusername',
                'tags'           => 'mailvotech.lead.import.label.tags',
            ],
        ], $event->fields);
    }

    public function testOnImportProcessForUnknownObject(): void
    {
        $subscriber = new ImportContactSubscriber(
            $this->getFieldListFake(),
            $this->getCorePermissionsFake(),
            $this->getLeadModelFake(),
            $this->getTranslatorFake()
        );
        $import = new Import();
        $import->setObject('unicorn');
        $event = new ImportProcessEvent($import, new LeadEventLog(), []);
        $subscriber->onImportProcess($event);
        $this->expectException(\UnexpectedValueException::class);
        $event->wasMerged();
    }

    public function testOnImportProcessForKnownObject(): void
    {
        $subscriber = new ImportContactSubscriber(
            $this->getFieldListFake(),
            $this->getCorePermissionsFake(),
            new class() extends LeadModel {
                public function __construct()
                {
                }

                public function import(array $fields, array $data, $owner = null, $list = null, $tags = null, bool $persist = true, ?LeadEventLog $eventLog = null, $importId = null, bool $skipIfExists = false): bool
                {
                    return true;
                }
            },
            $this->getTranslatorFake()
        );
        $import = new Import();
        $import->setObject('lead');
        $event = new ImportProcessEvent($import, new LeadEventLog(), []);
        $subscriber->onImportProcess($event);
        $this->assertTrue($event->wasMerged());
    }

    private function getFieldListFake(): FieldList
    {
        return new class() extends FieldList {
            public function __construct()
            {
            }
        };
    }

    private function getCorePermissionsFake(): CorePermissions
    {
        return new class() extends CorePermissions {
            public function __construct()
            {
            }
        };
    }

    private function getLeadModelFake(): LeadModel
    {
        return new class() extends LeadModel {
            public function __construct()
            {
            }
        };
    }

    private function getTranslatorFake(): TranslatorInterface
    {
        return new class() extends Translator {
            public function __construct()
            {
            }
        };
    }
}
