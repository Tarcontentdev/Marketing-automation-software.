<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
final class PublishUpDateType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['label' => 'mailvotech.core.form.activate_at']);
    }

    public function getParent(): string
    {
        return DatePickerType::class;
    }
}
