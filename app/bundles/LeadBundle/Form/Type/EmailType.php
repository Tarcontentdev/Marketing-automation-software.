<?php

namespace MailVotech\LeadBundle\Form\Type;

use MailVotech\CoreBundle\Form\EventListener\CleanFormSubscriber;
use MailVotech\CoreBundle\Form\Type\FormButtonsType;
use MailVotech\CoreBundle\Helper\UserHelper;
use MailVotech\EmailBundle\Form\Type\EmailListType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @extends AbstractType<mixed>
 */
final class EmailType extends AbstractType
{
    public const REPLY_TO_ADDRESS = 'replyToAddress';

    public function __construct(
        private readonly UserHelper $userHelper,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventSubscriber(new CleanFormSubscriber(['body' => 'raw']));

        $builder->add(
            'subject',
            TextType::class,
            [
                'label'       => 'mailvotech.email.subject',
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => ['class' => 'form-control'],
                'constraints' => [
                    new NotBlank(message: 'mailvotech.core.subject.required'),
                ],
            ]
        );

        $user = $this->userHelper->getUser();

        $default = (empty($options['data']['fromname'])) ? $user->getFirstName().' '.$user->getLastName() : $options['data']['fromname'];
        $builder->add(
            'fromname',
            TextType::class,
            [
                'label'      => 'mailvotech.lead.email.from_name',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'    => 'form-control',
                    'preaddon' => 'ri-user-6-fill',
                ],
                'required'   => false,
                'data'       => $default,
            ]
        );

        $default = (empty($options['data']['from'])) ? $user->getEmail() : $options['data']['from'];
        $builder->add(
            'from',
            TextType::class,
            [
                'label'       => 'mailvotech.lead.email.from_email',
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => [
                    'class'    => 'form-control',
                    'preaddon' => 'ri-mail-line',
                ],
                'required'    => false,
                'data'        => $default,
                'constraints' => [
                    new NotBlank(message: 'mailvotech.core.email.required'),
                    new Email(message: 'mailvotech.core.email.required'),
                ],
            ]
        );

        $builder->add(
            self::REPLY_TO_ADDRESS,
            TextType::class,
            [
                'label'      => 'mailvotech.email.reply_to_email',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'    => 'form-control',
                    'preaddon' => 'ri-mail-line',
                    'tooltip'  => 'mailvotech.email.reply_to_email.tooltip',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'body',
            TextareaType::class,
            [
                'label'      => 'mailvotech.email.form.body',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'                => 'form-control editor editor-basic-fullpage editor-builder-tokens editor-email',
                    'data-token-callback'  => 'email:getBuilderTokens',
                    'data-token-activator' => '{',
                    'allow-full-html'      => true,
                ],
                'constraints' => [
                    new Callback(callback: function ($value, ExecutionContextInterface $context): void {
                        if ('' === trim(strip_tags($value))) {
                            $context->buildViolation('mailvotech.lead.email.body.required')->addViolation();
                        }
                    }),
                ],
            ]
        );

        $builder->add('list', HiddenType::class);

        $builder->add(
            'templates',
            EmailListType::class,
            [
                'label'      => 'mailvotech.lead.email.template',
                'label_attr' => ['class' => 'control-label'],
                'required'   => false,
                'attr'       => [
                    'class'    => 'form-control',
                    'onchange' => 'MailVotech.getLeadEmailContent(this)',
                ],
                'multiple' => false,
            ]
        );

        $builder->add('buttons', FormButtonsType::class, [
            'apply_text'  => false,
            'save_text'   => 'mailvotech.email.send',
            'save_class'  => 'btn btn-primary',
            'save_icon'   => 'ri-send-plane-line',
            'cancel_icon' => 'ri-close-line',
        ]);

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    public function getBlockPrefix(): string
    {
        return 'lead_quickemail';
    }
}
