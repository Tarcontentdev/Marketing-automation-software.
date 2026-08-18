<?php

declare(strict_types=1);

namespace MailVotech\WebhookBundle\Form\Type;

use MailVotech\CoreBundle\Form\Type\SortableListType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractType<array<mixed>>
 */
final class CampaignEventSendWebhookType extends AbstractType
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'url',
            UrlType::class,
            [
                'label'       => 'mailvotech.webhook.event.sendwebhook.url',
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => ['class' => 'form-control'],
                'required'    => true,
                'constraints' => [
                    new NotBlank(
                        message: 'mailvotech.core.value.required'
                    ),
                ],
            ]
        );

        $builder->add(
            'method',
            ChoiceType::class,
            [
                'choices' => [
                    'GET'    => 'get',
                    'POST'   => 'post',
                    'PUT'    => 'put',
                    'PATCH'  => 'patch',
                    'DELETE' => 'delete',
                ],
                'multiple'   => false,
                'label_attr' => ['class' => 'control-label'],
                'label'      => 'mailvotech.webhook.event.sendwebhook.method',
                'attr'       => [
                    'class' => 'form-control',
                ],
                'placeholder'       => false,
                'required'          => false,
            ]
        );

        $builder->add(
            'headers',
            SortableListType::class,
            [
                'required'        => false,
                'label'           => 'mailvotech.webhook.event.sendwebhook.headers',
                'option_required' => false,
                'with_labels'     => true,
            ]
        );

        $builder->add(
            'additional_data',
            SortableListType::class,
            [
                'required'        => false,
                'label'           => 'mailvotech.webhook.event.sendwebhook.data',
                'option_required' => false,
                'with_labels'     => true,
            ]
        );

        $builder->add(
            'timeout',
            NumberType::class,
            [
                'label'      => 'mailvotech.webhook.event.sendwebhook.timeout',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'          => 'form-control',
                    'postaddon_text' => $this->translator->trans('mailvotech.core.time.seconds'),
                ],
                'data' => !empty($options['data']['timeout']) ? $options['data']['timeout'] : 10,
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'campaignevent_sendwebhook';
    }
}
