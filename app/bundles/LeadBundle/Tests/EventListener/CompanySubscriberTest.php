<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\EventListener;

use Doctrine\ORM\EntityManager;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Helper\IpLookupHelper;
use MailVotech\CoreBundle\Model\AuditLogModel;
use MailVotech\LeadBundle\Entity\Company;
use MailVotech\LeadBundle\Entity\CompanyLeadRepository;
use MailVotech\LeadBundle\Event\CompanyEvent;
use MailVotech\LeadBundle\EventListener\CompanySubscriber;
use MailVotech\LeadBundle\LeadEvents;
use MailVotech\LeadBundle\Model\CompanyModel;

final class CompanySubscriberTest extends \PHPUnit\Framework\TestCase
{
    public function testGetSubscribedEvents(): void
    {
        $ipLookupHelper        = $this->createStub(IpLookupHelper::class);
        $auditLogModel         = $this->createStub(AuditLogModel::class);
        $entityManager         = $this->createStub(EntityManager::class);
        $coreParameters        = $this->createStub(CoreParametersHelper::class);
        $companyLeadRepository = $this->createStub(CompanyLeadRepository::class);
        $companyModel          = $this->createStub(CompanyModel::class);
        $subscriber            = new CompanySubscriber(
            $ipLookupHelper,
            $auditLogModel,
            $entityManager,
            $coreParameters,
            $companyLeadRepository,
            $companyModel
        );

        $this->assertSame(
            [
                LeadEvents::COMPANY_PRE_SAVE    => ['onCompanyPreSave', 0],
                LeadEvents::COMPANY_POST_SAVE   => ['onCompanyPostSave', 0],
                LeadEvents::COMPANY_POST_DELETE => ['onCompanyDelete', 0],
                LeadEvents::COMPANY_SOFT_DELETE => ['onCompanySoftDelete', 0],
            ],
            $subscriber->getSubscribedEvents()
        );
    }

    public function testOnCompanyPostSave(): void
    {
        $this->onCompanyPostSaveMethodCall(false); // update company log
        $this->onCompanyPostSaveMethodCall(true); // create company log
    }

    public function testOnCompanyDelete(): void
    {
        $companyId        = 1;
        $companyName      = 'name';
        $ip               = '127.0.0.2';

        $log = [
            'bundle'    => 'lead',
            'object'    => 'company',
            'objectId'  => $companyId,
            'action'    => 'delete',
            'details'   => ['name', $companyName],
            'ipAddress' => $ip,
        ];

        $ipLookupHelper = $this->createMock(IpLookupHelper::class);
        $ipLookupHelper->expects($this->once())
            ->method('getIpAddressFromRequest')
            ->willReturn($ip);

        $auditLogModel = $this->createMock(AuditLogModel::class);
        $auditLogModel->expects($this->once())
            ->method('writeToLog')
            ->with($log);

        $entityManager         = $this->createStub(EntityManager::class);
        $coreParameters        = $this->createStub(CoreParametersHelper::class);
        $companyLeadRepository = $this->createStub(CompanyLeadRepository::class);
        $companyModel          = $this->createStub(CompanyModel::class);
        $subscriber            = new CompanySubscriber(
            $ipLookupHelper,
            $auditLogModel,
            $entityManager,
            $coreParameters,
            $companyLeadRepository,
            $companyModel,
        );

        $company            = $this->createMock(Company::class);
        $company->deletedId = $companyId;
        $company->expects($this->once())
            ->method('getPrimaryIdentifier')
            ->willReturn($companyName);

        $event = $this->createMock(CompanyEvent::class);
        $event->expects($this->once())
            ->method('getCompany')
            ->willReturn($company);

        $subscriber->onCompanyDelete($event);
    }

    /**
     * Test create or update company logging.
     */
    private function onCompanyPostSaveMethodCall(bool $isNew): void
    {
        $companyId = 1;
        $changes   = ['changes'];
        $ip        = '127.0.0.2';

        $log = [
            'bundle'    => 'lead',
            'object'    => 'company',
            'objectId'  => $companyId,
            'action'    => ($isNew) ? 'create' : 'update',
            'details'   => $changes,
            'ipAddress' => $ip,
        ];

        $ipLookupHelper = $this->createMock(IpLookupHelper::class);
        $ipLookupHelper->expects($this->once())
            ->method('getIpAddressFromRequest')
            ->willReturn($ip);

        $auditLogModel = $this->createMock(AuditLogModel::class);
        $auditLogModel->expects($this->once())
            ->method('writeToLog')
            ->with($log);
        $subscriber            = new CompanySubscriber(
            $ipLookupHelper,
            $auditLogModel,
            $this->createStub(EntityManager::class),
            $this->createStub(CoreParametersHelper::class),
            $this->createStub(CompanyLeadRepository::class),
            $this->createStub(CompanyModel::class),
        );

        $company = $this->createMock(Company::class);
        $company->expects($this->once())
            ->method('getId')
            ->willReturn($companyId);

        $event = $this->createMock(CompanyEvent::class);
        $event->expects($this->once())
            ->method('getCompany')
            ->willReturn($company);
        $event->expects($this->once())
            ->method('getChanges')
            ->willReturn($changes);
        $event->expects($this->once())
            ->method('isNew')
            ->willReturn($isNew);

        $subscriber->onCompanyPostSave($event);
    }
}
