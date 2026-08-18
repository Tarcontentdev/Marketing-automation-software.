<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\Functional;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\LeadBundle\Entity\Company;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Entity\LeadField;
use MailVotech\LeadBundle\Model\CompanyModel;
use MailVotech\LeadBundle\Model\FieldModel;
use MailVotech\LeadBundle\Model\LeadModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractSearchTestCase extends MailVotechMysqlTestCase
{
    /**
     * @param array<string, string|array<string, string>> $data
     */
    protected function createContact(array $data): void
    {
        /** @var LeadModel $leadModel */
        $leadModel = static::getContainer()->get(LeadModel::class);

        $contact = (new Lead())
            ->setFirstname($data['firstname'])
            ->setLastname($data['lastname'])
            ->setEmail($data['email'])
            ->setCompany($data['company']);

        foreach ($data['customFields'] ?? [] as $key => $value) {
            $contact->addUpdatedField($key, $value);
        }

        $leadModel->saveEntity($contact);
    }

    protected function createSearchableField(string $name, string $object): void
    {
        $field = new LeadField();
        $field->setName($name);
        $field->setAlias($name);
        $field->setObject($object);
        $field->setDateAdded(new \DateTime());
        $field->setDateAdded(new \DateTime());
        $field->setDateModified(new \DateTime());
        $field->setIsIndex(true);
        $field->setType('text');

        $fieldModel = static::getContainer()->get(FieldModel::class);
        $fieldModel->saveEntity($field);
    }

    /**
     * @param array<string, string|array<string, string>> $data
     */
    protected function createCompany(array $data): void
    {
        /** @var CompanyModel $companyModel */
        $companyModel = static::getContainer()->get(CompanyModel::class);

        $company = (new Company())
            ->setName($data['name'] ?? null)
            ->setEmail($data['email'] ?? null);

        foreach ($data['customFields'] ?? [] as $key => $value) {
            $company->addUpdatedField($key, $value);
        }

        $companyModel->saveEntity($company);

        $this->em->clear();
    }

    protected function performSearch(string $url): Response
    {
        $this->client->xmlHttpRequest(Request::METHOD_GET, $url);
        $response = $this->client->getResponse();
        $this->assertResponseIsSuccessful();

        return $response;
    }
}
