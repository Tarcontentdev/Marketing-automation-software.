<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Form\Type;

use MailVotech\IntegrationsBundle\Exception\IntegrationNotFoundException;
use MailVotech\IntegrationsBundle\Helper\BuilderIntegrationsHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
final class DynamicContentFilterType extends AbstractType
{
    public function __construct(
        private readonly BuilderIntegrationsHelper $builderIntegrationsHelper,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $extraClasses = '';

        try {
            $mailvotechBuilder = $this->builderIntegrationsHelper->getBuilder('email');
            $mailvotechBuilder->getName();
        } catch (IntegrationNotFoundException) {
            // Assume legacy builder
            $extraClasses = ' legacy-builder';
        }

        $builder->add(
            'tokenName',
            TextType::class,
            [
                'label' => 'mailvotech.core.dynamicContent.token_name',
                'attr'  => [
                    'class' => 'form-control dynamic-content-token-name',
                ],
            ]
        );

        $builder->add(
            'content',
            TextareaType::class,
            [
                'label' => 'mailvotech.core.dynamicContent.default_content',
                'attr'  => [
                    'class' => 'form-control editor editor-dynamic-content'.$extraClasses,
                ],
            ]
        );

        $builder->add(
            $builder->create(
                'filters',
                DynamicListType::class,
                [
                    'entry_type'     => DynamicContentFilterEntryType::class,
                    'entry_options'  => [
                        'label' => false,
                        'attr'  => [
                            'class' => 'form-control',
                        ],
                    ],
                    'option_required' => false,
                    'allow_add'       => true,
                    'allow_delete'    => true,
                ]
            )
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'label'          => false,
                'error_bubbling' => false,
            ]
        );
    }
}
