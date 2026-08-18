<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\EventListener;

use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\LeadBundle\Entity\Import;
use MailVotech\LeadBundle\Entity\LeadEventLog;
use MailVotech\LeadBundle\Event\ImportInitEvent;
use MailVotech\LeadBundle\Event\ImportMappingEvent;
use MailVotech\LeadBundle\Event\ImportProcessEvent;
use MailVotech\LeadBundle\Event\ImportValidateEvent;
use MailVotech\LeadBundle\EventListener\ImportCompanySubscriber;
use MailVotech\LeadBundle\Field\FieldList;
use MailVotech\LeadBundle\Model\CompanyModel;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Form;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ImportCompanySubscriberTest extends \PHPUnit\Framework\TestCase
{
    public function testOnImportInitForUknownObject(): void
    {
        $subscriber = new ImportCompanySubscriber(
            $this->getFieldListFake(),
            $this->getCorePermissionsFake(),
            $this->getCompanyModelFake(),
            $this->getTranslatorFake()
        );
        $event = new ImportInitEvent('unicorn');
        $subscriber->onImportInit($event);
        $this->assertFalse($event->objectSupported);
    }

    public function testOnImportInitForContactsObjectWithoutPermissions(): void
    {
        $subscriber = new ImportCompanySubscriber(
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
            $this->getCompanyModelFake(),
            $this->getTranslatorFake()
        );
        $event = new ImportInitEvent('companies');
        $this->expectException(AccessDeniedException::class);
        $subscriber->onImportInit($event);
    }

    public function testOnImportInitForContactsObjectWithPermissions(): void
    {
        $subscriber = new ImportCompanySubscriber(
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
            $this->getCompanyModelFake(),
            $this->getTranslatorFake()
        );
        $event = new ImportInitEvent('companies');
        $subscriber->onImportInit($event);
        $this->assertTrue($event->objectSupported);
        $this->assertSame('company', $event->objectSingular);
        $this->assertSame('mailvotech.lead.lead.companies', $event->objectName);
        $this->assertSame('#mailvotech_company_index', $event->activeLink);
        $this->assertSame('mailvotech_company_index', $event->indexRoute);
    }

    public function testOnFieldMappingForUnknownObject(): void
    {
        $subscriber = new ImportCompanySubscriber(
            $this->getFieldListFake(),
            $this->getCorePermissionsFake(),
            $this->getCompanyModelFake(),
            $this->getTranslatorFake()
        );
        $event = new ImportMappingEvent('unicorn');
        $subscriber->onFieldMapping($event);
        $this->assertFalse($event->objectSupported);
    }

    public function testOnFieldMapping(): void
    {
        $subscriber = new ImportCompanySubscriber(
            new class() extends FieldList {
                public function __construct()
                {
                }

                /**
                 * @param array<string, mixed> $filters
                 *
                 * @return array<string>
                 */
                public function getFieldList(bool $byGroup = true, bool $alphabetical = true, array $filters = ['isPublished' => true, 'object' => 'lead']): array
                {
                    return ['some fields'];
                }
            },
            $this->getCorePermissionsFake(),
            $this->getCompanyModelFake(),
            $this->getTranslatorFake()
        );
        $event = new ImportMappingEvent('companies');
        $subscriber->onFieldMapping($event);
        $this->assertTrue($event->objectSupported);
        $this->assertSame([
            'mailvotech.lead.company' => [
                'some fields',
            ],
            'mailvotech.lead.special_fields' => [
                'dateAdded'      => 'mailvotech.lead.import.label.dateAdded',
                'createdByUser'  => 'mailvotech.lead.import.label.createdByUser',
                'dateModified'   => 'mailvotech.lead.import.label.dateModified',
                'modifiedByUser' => 'mailvotech.lead.import.label.modifiedByUser',
            ],
        ], $event->fields);
    }

    public function testOnImportProcessForUnknownObject(): void
    {
        $subscriber = new ImportCompanySubscriber(
            $this->getFieldListFake(),
            $this->getCorePermissionsFake(),
            $this->getCompanyModelFake(),
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
        $subscriber = new ImportCompanySubscriber(
            $this->getFieldListFake(),
            $this->getCorePermissionsFake(),
            new class() extends CompanyModel {
                public function __construct()
                {
                }

                public function import(array $fields, array $data, $owner = null, bool $skipIfExists = false): bool
                {
                    return true;
                }
            },
            $this->getTranslatorFake()
        );
        $import = new Import();
        $import->setObject('company');
        $event = new ImportProcessEvent($import, new LeadEventLog(), []);
        $subscriber->onImportProcess($event);
        $this->assertTrue($event->wasMerged());
    }

    public function testImportCompanySubscriberDoesHaveTranslatorInitialized(): void
    {
        /** @var FieldList&MockObject $fieldListMock */
        $fieldListMock         = $this->createMock(FieldList::class);
        $missingRequiredFields = ['Company Name'];
        $matchedFields         = ['Company Email'];
        $fieldListMock->expects($this->once())
            ->method('getFieldList')
            ->with(false, false, [
                'isPublished' => true,
                'object'      => 'company',
                'isRequired'  => true,
            ])
            ->willReturn($missingRequiredFields);

        /** @var TranslatorInterface&MockObject $translatorInterfaceMock */
        $translatorInterfaceMock = $this->createMock(TranslatorInterface::class);
        $subscriber              = new ImportCompanySubscriber(
            $fieldListMock,
            $this->getCorePermissionsFake(),
            $this->getCompanyModelFake(),
            $translatorInterfaceMock
        );

        /** @var ImportValidateEvent&MockObject $importValidateEventMock */
        $importValidateEventMock = $this->createMock(ImportValidateEvent::class);
        $importValidateEventMock->expects($this->once())
            ->method('importIsForRouteObject')
            ->with('companies')
            ->willReturn(true);

        /** @var Form&MockObject $formMock */
        $formMock = $this->createMock(Form::class);
        $importValidateEventMock->expects($this->exactly(2))
            ->method('getForm')
            ->willReturn($formMock);
        $formMock->expects($this->once())
            ->method('getData')
            ->willReturn($matchedFields);
        $translatorInterfaceMock->expects($this->once())
            ->method('trans')
            ->with(
                'mailvotech.import.missing.required.fields',
                [
                    '%requiredFields%' => implode(', ', $missingRequiredFields),
                    '%fieldOrFields%'  => 'field',
                ],
                'validators'
            )->willReturn('A translated message');

        $subscriber->onValidateImport($importValidateEventMock);
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

    private function getCompanyModelFake(): CompanyModel
    {
        return new class() extends CompanyModel {
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
