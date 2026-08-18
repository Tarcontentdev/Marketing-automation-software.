<?php

namespace Utils\Rector\Tests\GetRepositoryToRepositoryServiceRector\Fixture;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\PageBundle\Entity\Hit;

final class TestCaseUsesContainer extends MailVotechMysqlTestCase
{
    public function testSomething(): void
    {
        $hitRepository = $this->em->getRepository(Hit::class);
    }
}

?>
-----
<?php

namespace Utils\Rector\Tests\GetRepositoryToRepositoryServiceRector\Fixture;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\PageBundle\Entity\Hit;

final class TestCaseUsesContainer extends MailVotechMysqlTestCase
{
    public function testSomething(): void
    {
        $hitRepository = self::getContainer()->get(\MailVotech\PageBundle\Entity\HitRepository::class);
    }
}

?>
