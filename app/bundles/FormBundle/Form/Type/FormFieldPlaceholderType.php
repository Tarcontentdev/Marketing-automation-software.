<?php

declare(strict_types=1);

namespace MailVotech\FormBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @extends AbstractType<mixed>
 */
final class FormFieldPlaceholderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('placeholder', TextType::class, [
            'label'      => 'mailvotech.form.field.form.property_placeholder',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => ['class' => 'form-control'],
            'required'   => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'formfield_placeholder';
    }
}
