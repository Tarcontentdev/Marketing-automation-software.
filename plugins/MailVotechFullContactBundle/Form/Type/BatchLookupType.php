<?php

namespace MailVotechPlugin\MailVotechFullContactBundle\Form\Type;

use MailVotech\CoreBundle\Form\Type\FormButtonsType;
use MailVotech\CoreBundle\Form\Type\YesNoButtonGroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @extends AbstractType<array<string, mixed>>
 */
final class BatchLookupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('ids', HiddenType::class);

        $builder->add(
            'buttons',
            FormButtonsType::class,
            [
                'apply_text'     => false,
                'save_text'      => 'mailvotech.core.form.submit',
                'cancel_onclick' => 'javascript:void(0);',
                'cancel_attr'    => [
                    'data-dismiss' => 'modal',
                ],
            ]
        );

        $builder->add(
            'notify',
            YesNoButtonGroupType::class,
            [
                'label'      => 'mailvotech.plugin.fullcontact.notify',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class' => 'form-control',
                ],
                'data'     => true,
                'required' => false,
            ]
        );

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    public function getBlockPrefix(): string
    {
        return 'fullcontact_batch_lookup';
    }
}
