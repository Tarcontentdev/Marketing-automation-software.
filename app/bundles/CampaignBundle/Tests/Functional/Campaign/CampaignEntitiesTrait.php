<?php

declare(strict_types=1);

namespace MailVotech\CampaignBundle\Tests\Functional\Campaign;

use Doctrine\ORM\Exception\ORMException;
use MailVotech\CampaignBundle\Entity\Campaign;
use MailVotech\CampaignBundle\Entity\Event;
use MailVotech\LeadBundle\Entity\Company;
use MailVotech\LeadBundle\Entity\CompanyLead;
use MailVotech\LeadBundle\Entity\CompanyRepository;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Entity\LeadField;
use MailVotech\LeadBundle\Entity\LeadList;
use MailVotech\LeadBundle\Entity\LeadRepository;
use MailVotech\LeadBundle\Entity\ListLead;
use MailVotech\LeadBundle\Model\CompanyModel;
use MailVotech\LeadBundle\Model\FieldModel;
use MailVotech\LeadBundle\Model\LeadModel;

trait CampaignEntitiesTrait
{
    /**
     * @param array<mixed> $fieldDetails
     */
    private function makeField(array $fieldDetails): void
    {
        $field = new LeadField();
        $field->setLabel($fieldDetails['alias']);
        $field->setType($fieldDetails['type']);
        $field->setObject($fieldDetails['object'] ?? 'lead');
        $field->setGroup($fieldDetails['group'] ?? 'core');
        $field->setAlias($fieldDetails['alias']);
        $field->setProperties($fieldDetails['properties']);

        $fieldModel = self::getContainer()->get(FieldModel::class);
        \assert($fieldModel instanceof FieldModel);
        $fieldModel->saveEntity($field);
    }

    /**
     * @param array<mixed> $filters
     *
     * @throws ORMException
     */
    protected function createSegment(string $alias, array $filters): LeadList
    {
        $segment = new LeadList();
        $segment->setAlias($alias);
        $segment->setPublicName($alias);
        $segment->setName($alias);
        $segment->setFilters($filters);
        $this->em->persist($segment);

        return $segment;
    }

    /**
     * @param array<mixed> $fieldDetails
     * @param array<mixed> $additionalValue
     */
    private function createLeadData(
        LeadList $segment,
        string $object,
        array $fieldDetails,
        array $additionalValue,
        int $index,
    ): Lead {
        $fieldValue      = [] !== $fieldDetails ?
            array_merge($fieldDetails, ['value' => array_merge(['v'.$index], $additionalValue)]) : [];
        $leadFieldValue  = 'lead' === $object ? $fieldValue : [];
        $lead            = $this->createLead('l'.$index, $leadFieldValue);
        if ('company' === $object) {
            $company = $this->createCompany('c'.$index, $fieldValue);
            $this->createCompanyLeadRelation($company, $lead);
        }
        $this->createSegmentMember($segment, $lead);

        return $lead;
    }

    /**
     * @param array<mixed> $customField
     */
    protected function createLead(string $leadName, array $customField = []): Lead
    {
        $contactRepo = $this->em->getRepository(Lead::class);
        \assert($contactRepo instanceof LeadRepository);
        $lead        = new Lead();
        $lead->setFirstname($leadName);
        if ([] !== $customField) {
            $lead->setFields([
                $customField['group'] => [
                    $customField['alias'] => [
                        'value' => '',
                        'alias' => $customField['alias'],
                        'type'  => $customField['type'],
                    ],
                ],
            ]);
            $leadModel = self::getContainer()->get(LeadModel::class);
            \assert($leadModel instanceof LeadModel);
            $leadModel->setFieldValues($lead, [$customField['alias'] => $customField['value']]);
        }
        $contactRepo->saveEntity($lead);

        return $lead;
    }

    /**
     * @param array<mixed> $customField
     */
    public function createCompany(string $name, array $customField = []): Company
    {
        $companyRepo = $this->em->getRepository(Company::class);
        \assert($companyRepo instanceof CompanyRepository);
        $company = new Company();
        $company->setName($name);
        if ([] !== $customField) {
            $company->setFields([
                $customField['group'] => [
                    $customField['alias'] => [
                        'value' => '',
                        'type'  => $customField['type'],
                    ],
                ],
            ]);
            $companyModel = self::getContainer()->get(CompanyModel::class);
            \assert($companyModel instanceof CompanyModel);
            $companyModel->setFieldValues($company, [$customField['alias'] => $customField['value']]);
        }
        $companyRepo->saveEntity($company);

        return $company;
    }

    private function createCompanyLeadRelation(Company $company, Lead $lead): void
    {
        $companyLead = new CompanyLead();
        $companyLead->setCompany($company);
        $companyLead->setLead($lead);
        $companyLead->setDateAdded(new \DateTime());

        $this->em->persist($companyLead);
    }

    /**
     * @throws ORMException
     */
    private function createSegmentMember(LeadList $segment, Lead $lead): void
    {
        $segmentMember = new ListLead();
        $segmentMember->setLead($lead);
        $segmentMember->setList($segment);
        $segmentMember->setDateAdded(new \DateTime());
        $this->em->persist($segmentMember);
    }

    /**
     * @throws ORMException
     */
    private function createCampaign(string $campaignName, LeadList $segment): Campaign
    {
        $campaign = new Campaign();
        $campaign->setName($campaignName);
        $campaign->setIsPublished(true);
        $campaign->addList($segment);
        $this->em->persist($campaign);

        return $campaign;
    }

    /**
     * @param array<mixed> $property
     *
     * @throws ORMException
     */
    protected function createEvent(
        string $name,
        Campaign $campaign,
        string $type,
        string $eventType,
        ?array $property = null,
        string $decisionPath = '',
        ?Event $parentEvent = null,
    ): Event {
        $event = new Event();
        $event->setName($name);
        $event->setCampaign($campaign);
        $event->setType($type);
        $event->setEventType($eventType);
        $event->setTriggerInterval(1);
        $event->setProperties($property);
        $event->setTriggerMode('immediate');
        $event->setDecisionPath($decisionPath);
        $event->setParent($parentEvent);
        $this->em->persist($event);

        return $event;
    }
}
