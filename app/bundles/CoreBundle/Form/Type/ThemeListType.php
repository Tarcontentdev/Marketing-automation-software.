<?php

namespace MailVotech\CoreBundle\Form\Type;

use MailVotech\CoreBundle\Helper\ThemeHelperInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
final class ThemeListType extends AbstractType
{
    public function __construct(
        private readonly ThemeHelperInterface $themeHelper,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'choices'           => function (Options $options): array {
                    $themes = $this->themeHelper->getInstalledThemes($options['feature']);
                    if ($options['include_code_mode']) {
                        $themes['mailvotech_code_mode'] = 'Code Mode';
                    }

                    return array_flip($themes);
                },
                'expanded'          => false,
                'multiple'          => false,
                'label'             => 'mailvotech.core.form.theme',
                'label_attr'        => ['class' => 'control-label'],
                'placeholder'       => false,
                'required'          => false,
                'attr'              => ['class' => 'form-control'],
                'feature'           => 'all',
                'include_code_mode' => true,
            ]
        );
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
