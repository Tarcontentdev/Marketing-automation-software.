<?php

declare(strict_types=1);

namespace MailVotech\StageBundle\Form\Type;

use MailVotech\CoreBundle\Form\Type\FormButtonsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @extends AbstractType<mixed>
 */
final class StageMergeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $stageChoices = $this->getStageChoices($options['stages']);

        $builder->add(
            'stage_to_merge',
            ChoiceType::class,
            [
                'choices'     => $stageChoices,
                'multiple'    => false,
                'label'       => 'mailvotech.stage.to.merge.into',
                'required'    => true,
                'placeholder' => 'mailvotech.core.form.chooseone',
                'constraints' => [
                    new NotBlank(
                        message: 'mailvotech.core.value.required'
                    ),
                    new Choice(choices: array_values($stageChoices), message: 'mailvotech.core.value.invalid'),
                ],
            ]
        );
        $builder->add(
            'buttons',
            FormButtonsType::class,
            [
                'apply_text' => false,
                'save_text'  => 'mailvotech.lead.merge',
                'save_icon'  => 'ri-flag-line',
            ]
        );

        if (null !== $options['action'] && '' !== $options['action']) {
            $builder->setAction($options['action']);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['stages']);
        $resolver->setAllowedTypes('stages', 'array');
    }

    /**
     * @param array<int, array{id: int|string, name: string}> $stages
     *
     * @return array<string, int|string>
     */
    private function getStageChoices(array $stages): array
    {
        $choices = [];

        foreach ($stages as $stage) {
            $choices[$stage['name']] = $stage['id'];
        }

        return $choices;
    }
}
