<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechFocusBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @extends AbstractType<array<string, mixed>>
 */
final class FocusShowType extends AbstractType
{
    public function __construct(
        private readonly RouterInterface $router,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'focus',
            FocusListType::class,
            [
                'label'      => 'mailvotech.focus.focusitem.selectitem',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'    => 'form-control',
                    'tooltip'  => 'mailvotech.focus.focusitem.selectitem_descr',
                    'onchange' => 'MailVotech.disabledFocusActions()',
                ],
                'multiple'    => false,
                'required'    => true,
                'constraints' => [
                    new NotBlank(
                        message: 'mailvotech.focus.choosefocus.notblank'
                    ),
                ],
                'data' => $options['data']['focus'] ?? null,
            ]
        );

        if (!empty($options['update_select'])) {
            $windowUrl = $this->router->generate(
                'mailvotech_focus_action',
                [
                    'objectAction' => 'new',
                    'contentOnly'  => 1,
                    'updateSelect' => $options['update_select'],
                ]
            );

            $builder->add(
                'newFocusButton',
                ButtonType::class,
                [
                    'attr' => [
                        'class'   => 'btn btn-primary btn-nospin',
                        'onclick' => 'MailVotech.loadNewWindow({
                        "windowUrl": "'.$windowUrl.'"
                    })',
                        'icon' => 'ri-add-line',
                    ],
                    'label' => 'mailvotech.focus.show.new.item',
                ]
            );

            // create button edit focus
            $windowUrlEdit = $this->router->generate(
                'mailvotech_focus_action',
                [
                    'objectAction' => 'edit',
                    'objectId'     => 'focusId',
                    'contentOnly'  => 1,
                    'updateSelect' => $options['update_select'],
                ]
            );

            $builder->add(
                'editFocusButton',
                ButtonType::class,
                [
                    'attr' => [
                        'class'    => 'btn btn-primary btn-nospin',
                        'onclick'  => 'MailVotech.loadNewWindow(MailVotech.standardFocusUrl({"windowUrl": "'.$windowUrlEdit.'"}))',
                        'disabled' => !isset($options['data']['focus']),
                        'icon'     => 'ri-edit-line',
                    ],
                    'label' => 'mailvotech.focus.show.edit.item',
                ]
            );
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined(['update_select']);
    }

    public function getBlockPrefix(): string
    {
        return 'focusshow_list';
    }
}
