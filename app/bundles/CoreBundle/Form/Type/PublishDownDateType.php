<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
final class PublishDownDateType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['label' => 'mailvotech.core.form.publishdown']);
    }

    public function getParent(): string
    {
        return DatePickerType::class;
    }
}
