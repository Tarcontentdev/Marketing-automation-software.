<?php

namespace Utils\Rector\Tests\GetRepositoryToRepositoryServiceRector\Fixture;

use MailVotech\PageBundle\Entity\Hit;
use MailVotech\PageBundle\Model\PageModel;
use Utils\Rector\Tests\GetRepositoryToRepositoryServiceRector\Source\AbstractParentWithConstructor;

final class ReuseExistingAutowireMethod extends AbstractParentWithConstructor
{
    private PageModel $pageModel;

    #[\Symfony\Contracts\Service\Attribute\Required]
    public function autowireReuseExistingAutowireMethod(PageModel $pageModel): void
    {
        $this->pageModel = $pageModel;
    }

    public function countHits(): int
    {
        return $this->entityManager->getRepository(Hit::class)->count([]);
    }
}

?>
-----
<?php

namespace Utils\Rector\Tests\GetRepositoryToRepositoryServiceRector\Fixture;

use MailVotech\PageBundle\Entity\Hit;
use MailVotech\PageBundle\Model\PageModel;
use Utils\Rector\Tests\GetRepositoryToRepositoryServiceRector\Source\AbstractParentWithConstructor;

final class ReuseExistingAutowireMethod extends AbstractParentWithConstructor
{
    private \MailVotech\PageBundle\Entity\HitRepository $hitRepository;
    private PageModel $pageModel;

    #[\Symfony\Contracts\Service\Attribute\Required]
    public function autowireReuseExistingAutowireMethod(PageModel $pageModel, \MailVotech\PageBundle\Entity\HitRepository $hitRepository): void
    {
        $this->pageModel = $pageModel;
        $this->hitRepository = $hitRepository;
    }

    public function countHits(): int
    {
        return $this->hitRepository->count([]);
    }
}

?>
