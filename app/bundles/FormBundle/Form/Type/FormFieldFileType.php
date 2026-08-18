<?php

namespace MailVotech\FormBundle\Form\Type;

use MailVotech\CoreBundle\Form\DataTransformer\ArrayStringTransformer;
use MailVotech\CoreBundle\Form\Type\YesNoButtonGroupType;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Helper\FileHelper;
use MailVotech\FormBundle\Validator\Constraint\FileExtensionConstraint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractType<mixed>
 */
final class FormFieldFileType extends AbstractType
{
    public const PROPERTY_ALLOWED_FILE_EXTENSIONS = 'allowed_file_extensions';

    public const PROPERTY_ALLOWED_FILE_SIZE       = 'allowed_file_size';

    public const PROPERTY_PREFERED_PROFILE_IMAGE  = 'profile_image';

    public function __construct(
        private readonly CoreParametersHelper $coreParametersHelper,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (empty($options['data'][self::PROPERTY_ALLOWED_FILE_EXTENSIONS])) {
            $options['data'][self::PROPERTY_ALLOWED_FILE_EXTENSIONS] = $this->coreParametersHelper->get('allowed_extensions');
        }
        if (empty($options['data'][self::PROPERTY_ALLOWED_FILE_SIZE])) {
            $options['data'][self::PROPERTY_ALLOWED_FILE_SIZE] = $this->coreParametersHelper->get('max_size');
        }

        $arrayStringTransformer = new ArrayStringTransformer();
        $builder->add(
            $builder->create(
                self::PROPERTY_ALLOWED_FILE_EXTENSIONS,
                TextareaType::class,
                [
                    'label'      => 'mailvotech.form.field.file.allowed_extensions',
                    'label_attr' => ['class' => 'control-label'],
                    'required'   => false,
                    'attr'       => [
                        'class'   => 'form-control',
                        'tooltip' => 'mailvotech.form.field.file.tooltip.allowed_extensions',
                    ],
                    'data'        => $options['data'][self::PROPERTY_ALLOWED_FILE_EXTENSIONS],
                    'constraints' => [new FileExtensionConstraint()],
                ]
            )->addViewTransformer($arrayStringTransformer)
        );

        $maxUploadSize = FileHelper::getMaxUploadSizeInMegabytes();
        $builder->add(
            self::PROPERTY_ALLOWED_FILE_SIZE,
            TextType::class,
            [
                'label'      => 'mailvotech.form.field.file.allowed_size',
                'label_attr' => ['class' => 'control-label'],
                'required'   => false,
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => $this->translator->trans('mailvotech.form.field.file.tooltip.allowed_size', ['%uploadSize%' => $maxUploadSize]),
                ],
                'data'        => $options['data'][self::PROPERTY_ALLOWED_FILE_SIZE],
                'constraints' => [new LessThanOrEqual(value: $maxUploadSize)],
            ]
        );

        $builder->add(
            'public',
            YesNoButtonGroupType::class,
            [
                'label' => 'mailvotech.form.field.file.public',
            ]
        );

        $builder->add(
            self::PROPERTY_PREFERED_PROFILE_IMAGE,
            YesNoButtonGroupType::class,
            [
                'label'       => 'mailvotech.form.field.file.set_as_profile_image',
                'data'        => $options['data'][self::PROPERTY_PREFERED_PROFILE_IMAGE] ?? false,
            ]
        );
    }
}
