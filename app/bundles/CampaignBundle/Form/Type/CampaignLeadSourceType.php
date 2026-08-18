<?php

namespace MailVotech\CampaignBundle\Form\Type;

use MailVotech\CoreBundle\Form\Type\FormButtonsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @extends AbstractType<mixed>
 */
final class CampaignLeadSourceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $sourceType    = $options['data']['sourceType'];
        $sourceChoices = $options['source_choices'] ?? [];
        foreach ($sourceChoices as $key => $val) {
            $sourceChoices[$key] = $val.' ('.$key.')';
        }

        switch ($sourceType) {
            case 'lists':
                $builder->add(
                    'lists',
                    ChoiceType::class,
                    [
                        'choices'           => array_flip($sourceChoices),
                        'multiple'          => true,
                        'label'             => 'mailvotech.campaign.leadsource.lists',
                        'label_attr'        => ['class' => 'control-label'],
                        'attr'              => [
                            'class' => 'form-control',
                        ],
                        'constraints' => [
                            new NotBlank(
                                message: 'mailvotech.core.value.required'
                            ),
                        ],
                    ]
                );
                break;
            case 'forms':
                $builder->add(
                    'forms',
                    ChoiceType::class,
                    [
                        'choices'           => array_flip($sourceChoices),
                        'multiple'          => true,
                        'label'             => 'mailvotech.campaign.leadsource.forms',
                        'label_attr'        => ['class' => 'control-label'],
                        'attr'              => [
                            'class' => 'form-control',
                        ],
                        'constraints' => [
                            new NotBlank(
                                message: 'mailvotech.core.value.required'
                            ),
                        ],
                    ]
                );
                break;
            default:
                break;
        }

        $builder->add('sourceType', HiddenType::class);

        $builder->add('droppedX', HiddenType::class);

        $builder->add('droppedY', HiddenType::class);

        $update = !empty($options['data'][$sourceType]);
        if (!empty($update)) {
            $btnValue = 'mailvotech.core.form.update';
            $btnIcon  = 'ri-edit-line';
        } else {
            $btnValue = 'mailvotech.core.form.add';
            $btnIcon  = 'ri-add-line';
        }

        $builder->add('buttons', FormButtonsType::class, [
            'save_text'       => $btnValue,
            'save_icon'       => $btnIcon,
            'save_onclick'    => 'MailVotech.submitCampaignSource(event)',
            'apply_text'      => false,
            'container_class' => 'bottom-form-buttons',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['source_choices']);
    }

    public function getBlockPrefix(): string
    {
        return 'campaign_leadsource';
    }
}
