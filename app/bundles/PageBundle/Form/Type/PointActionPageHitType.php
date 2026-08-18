<?php

declare(strict_types=1);

namespace MailVotech\PageBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @extends AbstractType<array<mixed>>
 */
final class PointActionPageHitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('pages', PageListType::class, [
            'label'      => 'mailvotech.page.point.action.form.pages',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => [
                'class'   => 'form-control',
                'tooltip' => 'mailvotech.page.point.action.form.pages.descr',
            ],
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'pointaction_pagehit';
    }
}
