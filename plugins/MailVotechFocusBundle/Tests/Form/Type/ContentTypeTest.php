<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechFocusBundle\Tests\Form\Type;

use MailVotechPlugin\MailVotechFocusBundle\Form\Type\ContentType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;

final class ContentTypeTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&FormBuilderInterface
     */
    private \PHPUnit\Framework\MockObject\MockObject $formBuilder;

    protected function setUp(): void
    {
        $this->formBuilder = $this->createMock(FormBuilderInterface::class);
    }

    public function testBuilderForm(): void
    {
        $this->formBuilder->expects($this->exactly(7))->method('add')->willReturnSelf();
        $options     = [];
        $contentType = new ContentType();
        $contentType->buildForm($this->formBuilder, $options);
    }
}
