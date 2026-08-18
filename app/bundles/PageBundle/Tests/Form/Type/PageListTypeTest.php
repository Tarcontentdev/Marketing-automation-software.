<?php

declare(strict_types=1);

namespace MailVotech\PageBundle\Tests\Form\Type;

use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\PageBundle\Entity\PageRepository;
use MailVotech\PageBundle\Form\Type\PageListType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PageListTypeTest extends TestCase
{
    private PageListType $page;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&PageRepository
     */
    private \PHPUnit\Framework\MockObject\MockObject $pageRepositoryMock;

    protected function setUp(): void
    {
        $this->pageRepositoryMock = $this->createMock(PageRepository::class);
        $this->page               = new PageListType($this->createStub(CorePermissions::class), $this->pageRepositoryMock);
    }

    public function testPageListTypeOptionsChoices(): void
    {
        $resolver = new OptionsResolver();

        $this->pageRepositoryMock->method('getPageList')
            ->willReturn([]);

        $this->page->configureOptions($resolver);

        $expectedOptions = [
            'placeholder' => false,
            'expanded'    => false,
            'multiple'    => true,
            'required'    => false,
            'top_level'   => 'variant',
            'ignore_ids'  => [],
            'choices'     => [],
        ];
        $this->assertEquals($expectedOptions, $resolver->resolve());
    }

    public function testGetParent(): void
    {
        $this->assertSame(ChoiceType::class, $this->page->getParent());
    }

    public function testGetBlockPrefix(): void
    {
        $this->assertSame('page_list', $this->page->getBlockPrefix());
    }
}
