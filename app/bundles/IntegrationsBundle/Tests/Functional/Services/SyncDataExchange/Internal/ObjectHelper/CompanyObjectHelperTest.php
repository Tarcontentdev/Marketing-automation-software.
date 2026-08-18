<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Tests\Functional\Services\SyncDataExchange\Internal\ObjectHelper;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Order\FieldDAO as OrderFieldDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Sync\Order\ObjectChangeDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Value\NormalizedValueDAO;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Internal\Object\Company as SyncObjectCompany;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Internal\ObjectHelper\CompanyObjectHelper;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\MailVotechSyncDataExchange;
use MailVotech\LeadBundle\Entity\Company;
use MailVotech\LeadBundle\Model\CompanyModel;
use MailVotech\UserBundle\Model\UserModel;

final class CompanyObjectHelperTest extends MailVotechMysqlTestCase
{
    public function testUpdateEmpty(): void
    {
        /** @var CompanyObjectHelper $companyObjectHelper */
        $companyObjectHelper  = self::getContainer()->get(CompanyObjectHelper::class);
        $updatedMappedObjects = $companyObjectHelper->update([], []);
        $this->assertSame([], $updatedMappedObjects);
    }

    public function testUpdate(): void
    {
        /** @var UserModel $userModel */
        $userModel = self::getContainer()->get(UserModel::class);
        $users     = $userModel->getRepository()->findAll();
        $user      = reset($users);
        $now       = new \DateTime();

        $company1 = new Company();
        $company1->setDateAdded($now);
        $company1->setOwner($user);

        $company2 = new Company();
        $company2->setDateAdded($now);
        $company2->setOwner($user);

        /** @var CompanyModel $companyModel */
        $companyModel = self::getContainer()->get(CompanyModel::class);
        $companyModel->saveEntity($company1);
        $companyModel->saveEntity($company2);

        $phone = '123456789';
        $city  = 'Boston';

        /** @var CompanyObjectHelper $companyObjectHelper */
        $companyObjectHelper = self::getContainer()->get(CompanyObjectHelper::class);
        $companyObjectHelper->update([
            $company1->getId(),
            $company2->getId(),
        ], [
            $company1->getId() => $this->buildObjectChangeDAO($company1, 'companyphone', $phone),
            $company2->getId() => $this->buildObjectChangeDAO($company2, 'companycity', $city),
        ]);

        $this->assertSame($phone, $company1->getPhone());
        $this->assertSame($city, $company2->getCity());
    }

    private function buildObjectChangeDAO(Company $company, string $name, string $value): ObjectChangeDAO
    {
        $objectChangeDAO = new ObjectChangeDAO('Test', MailVotechSyncDataExchange::OBJECT_COMPANY, $company->getId(), SyncObjectCompany::NAME, $company->getId(), new \DateTime());
        $objectChangeDAO->addField(new OrderFieldDAO($name, new NormalizedValueDAO(NormalizedValueDAO::PHONE_TYPE, $value)));

        return $objectChangeDAO;
    }
}
