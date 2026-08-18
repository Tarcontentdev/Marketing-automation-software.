<?php

namespace Utils\Rector\Tests\GetRepositoryToRepositoryServiceRector\Fixture;

use Doctrine\ORM\EntityManagerInterface;
use MailVotech\PageBundle\Entity\Hit;

final class ServiceUsesConstructorInjection
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
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

use Doctrine\ORM\EntityManagerInterface;
use MailVotech\PageBundle\Entity\Hit;

final class ServiceUsesConstructorInjection
{
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly \MailVotech\PageBundle\Entity\HitRepository $hitRepository)
    {
    }

    public function countHits(): int
    {
        return $this->hitRepository->count([]);
    }
}

?>
