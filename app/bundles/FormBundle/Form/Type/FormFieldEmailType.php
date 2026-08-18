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
final class FormFieldEmailType extends AbstractType
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'donotsubmit',
            YesNoButtonGroupType::class,
            [
                'label' => 'mailvotech.form.field.type.donotsubmit',
                'data'  => $options['data']['donotsubmit'] ?? false,
            ]
        );

        $builder->add(
            'donotsubmit_validationmsg',
            TextType::class,
            [
                'label'      => 'mailvotech.form.field.form.validationmsg',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'        => 'form-control',
                    'data-show-on' => '{"formfield_validation_donotsubmit_1": "checked"}',
                ],
                'data'     => $options['data']['donotsubmit_validationmsg'] ?? $this->translator->trans('mailvotech.form.submission.email.donotsubmit.invalid', [], 'validators'),
                'required' => false,
            ]
        );

        $builder->add(
            'blockfreeemail',
            YesNoButtonGroupType::class,
            [
                'label' => 'mailvotech.form.field.type.blockfreeemail',
                'attr'  => [
                    'tooltip' => 'mailvotech.form.field.type.blockfreeemail.tooltip',
                ],
                'data'  => $options['data']['blockfreeemail'] ?? false,
            ]
        );

        $builder->add(
            'blockfreeemail_validationmsg',
            TextType::class,
            [
                'label'      => 'mailvotech.form.field.form.validationmsg',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'        => 'form-control',
                    'data-show-on' => '{"formfield_validation_blockfreeemail_1": "checked"}',
                ],
                'data'     => $options['data']['blockfreeemail_validationmsg'] ?? $this->translator->trans('mailvotech.form.submission.email.freeproviders.invalid', [], 'validators'),
                'required' => false,
            ]
        );
    }
}
