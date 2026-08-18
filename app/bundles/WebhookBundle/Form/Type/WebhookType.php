<?php

namespace MailVotech\WebhookBundle\Form\Type;

use Doctrine\Common\Collections\Order;
use MailVotech\CategoryBundle\Form\Type\CategoryListType;
use MailVotech\CoreBundle\Form\EventListener\CleanFormSubscriber;
use MailVotech\CoreBundle\Form\Type\FormButtonsType;
use MailVotech\CoreBundle\Form\Type\YesNoButtonGroupType;
use MailVotech\CoreBundle\Helper\EncryptionHelper;
use MailVotech\WebhookBundle\Entity\Webhook;
use MailVotech\WebhookBundle\Form\DataTransformer\EventsToArrayTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Webhook>
 */
final class WebhookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventSubscriber(new CleanFormSubscriber(['description' => 'strict_html']));

        /** @var Webhook $webhook */
        $webhook = $builder->getData();

        $builder->add(
            'name',
            TextType::class,
            [
                'label'      => 'mailvotech.core.name',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
                'required'   => true,
            ]
        );

        $builder->add(
            'description',
            TextareaType::class,
            [
                'label'    => 'mailvotech.webhook.form.description',
                'required' => false,
                'attr'     => [
                    'class' => 'form-control',
                ],
            ]
        );

        $builder->add(
            'webhookUrl',
            UrlType::class,
            [
                'label'      => 'mailvotech.webhook.form.webhook_url',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
                'required'   => true,
            ]
        );

        $builder->add(
            'secret',
            TextType::class,
            [
                'label'      => 'mailvotech.webhook.form.secret',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'mailvotech.webhook.secret.tooltip',
                ],
                'data'     => $webhook->getSecret() ?? EncryptionHelper::generateKey(),
                'required' => false,
            ]
        );

        $events = $options['events'];

        $choices = [];
        foreach ($events as $type => $event) {
            $choices[$event['label']] = $type;
        }

        $builder->add(
            'events',
            ChoiceType::class,
            [
                'choices'    => $choices,
                'multiple'   => true,
                'expanded'   => true,
                'label'      => 'mailvotech.webhook.form.webhook.events',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => ''],
            ]
        );

        $builder->get('events')->addModelTransformer(new EventsToArrayTransformer($options['data']));

        $builder->add('buttons', FormButtonsType::class);

        $builder->add(
            'sendTest',
            ButtonType::class,
            [
                'attr'  => ['class' => 'btn btn-tertiary', 'onclick' => 'MailVotech.sendHookTest(this)'],
                'label' => 'mailvotech.webhook.send.test.payload',
            ]
        );

        $builder->add(
            'category',
            CategoryListType::class,
            [
                'bundle' => 'Webhook',
            ]
        );

        $builder->add('isPublished', YesNoButtonGroupType::class);

        $builder->add(
            'eventsOrderbyDir',
            ChoiceType::class,
            [
                'choices' => [
                    'mailvotech.webhook.config.event.orderby.chronological'         => Order::Ascending->value,
                    'mailvotech.webhook.config.event.orderby.reverse.chronological' => Order::Descending->value,
                ],
                'label' => 'mailvotech.webhook.config.event.orderby',
                'attr'  => [
                    'class'   => 'form-control',
                    'tooltip' => 'mailvotech.webhook.config.event.orderby.tooltip',
                ],
                'placeholder' => 'mailvotech.core.form.default',
                'required'    => false,
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Webhook::class,
            ]
        );

        $resolver->setDefined(['events']);
    }
}
