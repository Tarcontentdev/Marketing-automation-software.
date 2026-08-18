<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\Tests\MonitoredEmail\Processor\Bounce\Mapper;

use MailVotech\EmailBundle\MonitoredEmail\Exception\CategoryNotFound;
use MailVotech\EmailBundle\MonitoredEmail\Processor\Bounce\Definition\Category as Definition;
use MailVotech\EmailBundle\MonitoredEmail\Processor\Bounce\Mapper\CategoryMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(CategoryMapper::class)]
#[CoversClass(Definition::class)]
final class CategoryMapperTest extends \PHPUnit\Framework\TestCase
{
    #[TestDox('Test that the Category object is returned')]
    public function testCategoryIsMapped(): void
    {
        $category = CategoryMapper::map(Definition::ANTISPAM);

        $this->assertSame(Definition::ANTISPAM, $category->getCategory());
    }

    #[TestDox('Test that exception is thrown if a category is not found')]
    public function testExceptionIsThrownWithUnrecognizedCategory(): void
    {
        $this->expectException(CategoryNotFound::class);

        CategoryMapper::map('bippitybop');
    }
}
