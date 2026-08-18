<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractType<mixed>
 */
final class TimeFormatType extends AbstractType
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => [
                '24-'.$this->translator->trans('mailvotech.core.time.hour') => '24',
                '12-'.$this->translator->trans('mailvotech.core.time.hour') => '12',
            ],
            'expanded'    => false,
            'multiple'    => false,
            'label'       => 'mailvotech.core.type.time_format',
            'label_attr'  => ['class' => ''],
            'empty_value' => false,
            'required'    => false,
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
