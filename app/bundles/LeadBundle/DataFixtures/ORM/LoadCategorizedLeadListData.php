<?php

namespace MailVotech\LeadBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use MailVotech\CategoryBundle\Entity\CategoryRepository;
use MailVotech\CoreBundle\Helper\CsvHelper;
use MailVotech\LeadBundle\Entity\LeadList;
use MailVotech\LeadBundle\Entity\LeadListRepository;

final class LoadCategorizedLeadListData extends AbstractFixture implements OrderedFixtureInterface
{
    public function __construct(
        private readonly LeadListRepository $leadListRepository,
        private readonly CategoryRepository $categoryRepository,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $leadLists = CsvHelper::csv_to_array(__DIR__.'/fakecategorizedleadlistdata.csv');
        foreach ($leadLists as $leadList) {
            $category       = $this->categoryRepository->find($leadList['category']);
            $leadListEntity = new LeadList();
            $leadListEntity->setName($leadList['name']);
            $leadListEntity->setPublicName($leadList['publicname']);
            $leadListEntity->setAlias($leadList['alias']);
            $leadListEntity->setCategory($category);
            $this->leadListRepository->saveEntity($leadListEntity);
        }
    }

    public function getOrder(): int
    {
        return 1;
    }
}
