<?php

declare(strict_types=1);

namespace MailVotech\PageBundle\Tests\Form\Type;

use MailVotech\PageBundle\Form\Type\RedirectListType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RedirectListTypeTest extends TestCase
{
    private RedirectListType $form;

    protected function setUp(): void
    {
        $this->form = new RedirectListType();
    }

    public function testGetParent(): void
    {
        $this->assertSame(ChoiceType::class, $this->form->getParent());
    }

    public function testConfigureOptionsChoicesDefined(): void
    {
        $choices = [
            'mailvotech.page.form.redirecttype.permanent'     => 301,
            'mailvotech.page.form.redirecttype.temporary'     => 302,
            'mailvotech.page.form.redirecttype.303_temporary' => 303,
            'mailvotech.page.form.redirecttype.307_temporary' => 307,
            'mailvotech.page.form.redirecttype.308_permanent' => 308,
        ];

        $resolver = new OptionsResolver();
        $this->form->configureOptions($resolver);

        $expectedOptions = [
            'choices'    => $choices,
            'expanded'   => false,
            'multiple'   => false,
            'label'      => 'mailvotech.page.form.redirecttype',
            'label_attr' => [
                'class' => 'control-label',
            ],
            'placeholder' => false,
            'required'    => false,
            'attr'        => [
                'class' => 'form-control',
            ],
            'feature' => 'all',
        ];

        $this->assertSame($expectedOptions, $resolver->resolve());
    }

    public function testGetBlockPrefix(): void
    {
        $this->assertSame('redirect_list', $this->form->getBlockPrefix());
    }
}
