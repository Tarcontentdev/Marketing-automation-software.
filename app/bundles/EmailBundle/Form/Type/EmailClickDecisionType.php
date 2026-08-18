<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\Form\Type;

use MailVotech\CoreBundle\Form\Type\SortableListType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @extends AbstractType<mixed>
 */
final class EmailClickDecisionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'urls',
            SortableListType::class,
            [
                'label'           => 'mailvotech.email.click.urls.contains',
                'option_required' => false,
                'with_labels'     => false,
                'required'        => false,
            ]
        );
    }
}
