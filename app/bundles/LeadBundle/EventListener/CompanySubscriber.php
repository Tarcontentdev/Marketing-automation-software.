<?php

namespace MailVotech\LeadBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Helper\IpLookupHelper;
use MailVotech\CoreBundle\Model\AuditLogModel;
use MailVotech\LeadBundle\Entity\CompanyLeadRepository;
use MailVotech\LeadBundle\Event as Events;
use MailVotech\LeadBundle\LeadEvents;
use MailVotech\LeadBundle\Model\CompanyModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class CompanySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private IpLookupHelper $ipLookupHelper,
        private AuditLogModel $auditLogModel,
        private EntityManagerInterface $entityManager,
        private CoreParametersHelper $coreParametersHelper,
        private CompanyLeadRepository $companyLeadRepository,
        private CompanyModel $companyModel,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LeadEvents::COMPANY_PRE_SAVE    => ['onCompanyPreSave', 0],
            LeadEvents::COMPANY_POST_SAVE   => ['onCompanyPostSave', 0],
            LeadEvents::COMPANY_POST_DELETE => ['onCompanyDelete', 0],
            LeadEvents::COMPANY_SOFT_DELETE => ['onCompanySoftDelete', 0],
        ];
    }

    /**
     * Add a company entry to the audit log.
     */
    public function onCompanyPostSave(Events\CompanyEvent $event): void
    {
        $company = $event->getCompany();
        if ($details = $event->getChanges()) {
            $log = [
                'bundle'    => 'lead',
                'object'    => 'company',
                'objectId'  => $company->getId(),
                'action'    => ($event->isNew()) ? 'create' : 'update',
                'details'   => $details,
                'ipAddress' => $this->ipLookupHelper->getIpAddressFromRequest(),
            ];
            $this->auditLogModel->writeToLog($log);
        }
    }

    /**
     * Add a company delete entry to the audit log.
     */
    public function onCompanyDelete(Events\CompanyEvent $event): void
    {
        $company = $event->getCompany();
        $log     = [
            'bundle'    => 'lead',
            'object'    => 'company',
            'objectId'  => $company->deletedId,
            'action'    => 'delete',
            'details'   => ['name', $company->getPrimaryIdentifier()],
            'ipAddress' => $this->ipLookupHelper->getIpAddressFromRequest(),
        ];
        $this->auditLogModel->writeToLog($log);
        $this->clearCompanyInLeadsCompanyFields($company->getName());
    }

    public function onCompanySoftDelete(Events\CompanyEvent $event): void
    {
        if ($details = $event->getChanges()) {
            $log = [
                'bundle'    => 'lead',
                'object'    => 'company',
                'objectId'  => $event->getCompany()->getId(),
                'action'    => 'update',
                'details'   => $details,
                'ipAddress' => $this->ipLookupHelper->getIpAddressFromRequest(),
            ];
            $this->auditLogModel->writeToLog($log);
        }

        if ($this->coreParametersHelper->get('update_company_mapping_data_in_background', false)) {
            return;
        }
        $this->companyModel->changePrimaryCompanyToLatest($event->getCompany()->getId());
        $this->companyLeadRepository->deleteCompanyLeads($event->getCompany()->getId());
        $this->companyModel->permanentDeleteCompany($event->getCompany());
    }

    public function onCompanyPreSave(Events\CompanyEvent $event): void
    {
        if ($this->coreParametersHelper->get('update_company_mapping_data_in_background', false)) {
            return;
        }
        if ($event->getCompany()->isNew() || empty($event->getCompany()->getChanges()['fields']['companyname'])) {
            return;
        }
        $this->companyLeadRepository->updateLeadsPrimaryCompanyName($event->getCompany());
    }

    private function clearCompanyInLeadsCompanyFields(?string $companyName): void
    {
        if (null === $companyName) {
            return;
        }
        $connection = $this->entityManager->getConnection();
        $connection->executeStatement(
            'UPDATE '.MAILVOTECH_TABLE_PREFIX.'leads SET company = NULL WHERE company = :companyName',
            ['companyName' => $companyName]
        );
    }
}
