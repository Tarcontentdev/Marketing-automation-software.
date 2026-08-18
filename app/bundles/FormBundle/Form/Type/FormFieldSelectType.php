<?php

declare(strict_types=1);

namespace MailVotech\FormBundle\Form\Type;

use MailVotech\CoreBundle\Form\Type\YesNoButtonGroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
final class FormFieldSelectType extends AbstractType
{
    use SortableListTrait;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ('select' === $options['field_type']) {
            $this->addSortableList($builder, $options);
        }

        $builder->add(
            'placeholder',
            TextType::class,
            [
                'label'      => 'mailvotech.form.field.form.emptyvalue',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
                'required'   => false,
            ]
        );

        if (!empty($options['parentData'])) {
            $default = !empty($options['parentData']['properties']['multiple']);
        } else {
            $default = false;
        }
        $builder->add(
            'multiple',
            YesNoButtonGroupType::class,
            [
                'label' => 'mailvotech.form.field.form.multiple',
                'data'  => $default,
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'field_type' => 'select',
                'parentData' => [],
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'formfield_select';
    }
}
