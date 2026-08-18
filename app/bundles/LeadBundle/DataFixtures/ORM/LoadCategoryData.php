<?php

namespace MailVotech\LeadBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use MailVotech\CategoryBundle\Entity\Category;
use MailVotech\CategoryBundle\Entity\CategoryRepository;
use MailVotech\CoreBundle\Helper\CsvHelper;

final class LoadCategoryData extends AbstractFixture implements OrderedFixtureInterface
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $categories = CsvHelper::csv_to_array(__DIR__.'/fakecategorydata.csv');
        foreach ($categories as $category) {
            $categoryEntity = new Category();
            $categoryEntity->setTitle($category['categoryname']);
            $categoryEntity->setBundle($category['categorybundle']);
            $categoryEntity->setAlias($category['categoryalias']);
            $categoryEntity->setIsPublished($category['published']);
            $this->categoryRepository->saveEntity($categoryEntity);
        }
    }

    public function getOrder(): int
    {
        return 1;
    }
}
