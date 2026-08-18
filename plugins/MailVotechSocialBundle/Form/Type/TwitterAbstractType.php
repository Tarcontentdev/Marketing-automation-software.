<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechSocialBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @extends AbstractType<array<mixed>>
 */
abstract class TwitterAbstractType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
    }
}
