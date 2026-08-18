<?php

declare(strict_types=1);

namespace MailVotech\FormBundle\Form\Type;

use MailVotech\CoreBundle\Form\Type\YesNoButtonGroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractType<mixed>
 */
final class FormFieldTelType extends AbstractType
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'international',
            YesNoButtonGroupType::class,
            [
                'label' => 'mailvotech.form.field.type.tel.international',
                'data'  => $options['data']['international'] ?? false,
            ]
        );

        $builder->add(
            'international_validationmsg',
            TextType::class,
            [
                'label'      => 'mailvotech.form.field.form.validationmsg',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'        => 'form-control',
                    'tooltip'      => $this->translator->trans('mailvotech.core.form.default').': '.$this->translator->trans('mailvotech.form.submission.phone.invalid', [], 'validators'),
                    'data-show-on' => '{"formfield_validation_international_1": "checked"}',
                ],
                'required' => false,
            ]
        );
    }
}
