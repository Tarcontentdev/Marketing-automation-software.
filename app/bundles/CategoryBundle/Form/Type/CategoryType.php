<?php

namespace MailVotech\CategoryBundle\Form\Type;

use MailVotech\CategoryBundle\Entity\Category;
use MailVotech\CoreBundle\Form\EventListener\CleanFormSubscriber;
use MailVotech\CoreBundle\Form\EventListener\FormExitSubscriber;
use MailVotech\CoreBundle\Form\Type\FormButtonsType;
use MailVotech\CoreBundle\Form\Type\YesNoButtonGroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Category>
 */
final class CategoryType extends AbstractType
{
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventSubscriber(new CleanFormSubscriber([]));
        $builder->addEventSubscriber(new FormExitSubscriber('category.category', $options));

        if (!$options['data']->getId()) {
            // Do not allow custom bundle
            if (true == $options['show_bundle_select']) {
                // Create new category from category bundle - let user select the bundle
                $selected = $this->requestStack->getSession()->get('mailvotech.category.type', 'category');
                $builder->add(
                    'bundle',
                    CategoryBundlesType::class,
                    [
                        'label'      => 'mailvotech.core.type',
                        'label_attr' => ['class' => 'control-label'],
                        'attr'       => ['class' => 'form-control'],
                        'required'   => true,
                        'data'       => $selected,
                    ]
                );
            } else {
                // Create new category directly from another bundle - preset bundle
                $builder->add(
                    'bundle',
                    HiddenType::class,
                    [
                        'data' => $options['bundle'],
                    ]
                );
            }
        }

        $builder->add(
            'title',
            TextType::class,
            [
                'label'      => 'mailvotech.core.title',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
            ]
        );

        $builder->add(
            'description',
            TextType::class,
            [
                'label'      => 'mailvotech.core.description',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
                'required'   => false,
            ]
        );

        $builder->add(
            'alias',
            TextType::class,
            [
                'label'      => 'mailvotech.core.alias',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'mailvotech.category.form.alias.help',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'color',
            TextType::class,
            [
                'label'      => 'mailvotech.core.color',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                    'data-toggle' => 'color',
                ],
                'required' => false,
            ]
        );

        $builder->add('isPublished', YesNoButtonGroupType::class, [
            'label' => 'mailvotech.core.form.available',
        ]);

        $builder->add(
            'inForm',
            HiddenType::class,
            [
                'mapped' => false,
            ]
        );

        $builder->add('buttons', FormButtonsType::class,
            [
                'apply_text' => false,
            ]);

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class'         => Category::class,
                'show_bundle_select' => false,
                'bundle'             => function (Options $options) {
                    if (!$bundle = $options['data']->getBundle()) {
                        $bundle = 'category';
                    }

                    return $bundle;
                },
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'category_form';
    }
}
