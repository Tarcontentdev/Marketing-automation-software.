<?php

declare(strict_types=1);

namespace MailVotech\FormBundle\Form\Type;

use MailVotech\CoreBundle\Form\ToBcBccFieldsTrait;
use MailVotech\CoreBundle\Form\Type\YesNoButtonGroupType;
use MailVotech\EmailBundle\Form\Type\EmailListType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractType<mixed>
 */
final class SubmitActionEmailType extends AbstractType
{
    use FormFieldTrait;
    use ToBcBccFieldsTrait;

    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $data = $options['data']['subject'] ?? $this->translator->trans(
            'mailvotech.form.action.sendemail.subject.default'
        );
        $builder->add(
            'subject',
            TextType::class,
            [
                'label'      => 'mailvotech.form.action.sendemail.subject',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
                'required'   => false,
                'data'       => $data,
            ]
        );

        if (!isset($options['data']['message'])) {
            $fields  = $this->getFormFields($options['attr']['data-formid']);
            $message = '';

            foreach ($fields as $token => $label) {
                $message .= "<strong>{$label}</strong>: {$token}<br />";
            }
        } else {
            $message = $options['data']['message'];
        }

        $builder->add(
            'message',
            TextareaType::class,
            [
                'label'      => 'mailvotech.form.action.sendemail.message',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control editor editor-basic', 'allow-full-html' => true],
                'required'   => false,
                'data'       => $message,
            ]
        );

        $builder->add(
            'immediately',
            HiddenType::class,
            [
                'data' => false,
            ]
        );

        $default = $options['data']['copy_lead'] ?? false;
        $builder->add(
            'copy_lead',
            YesNoButtonGroupType::class,
            [
                'label' => 'mailvotech.form.action.sendemail.copytolead',
                'data'  => $default,
            ]
        );

        $default = $options['data']['set_replyto'] ?? true;
        $builder->add(
            'set_replyto',
            YesNoButtonGroupType::class,
            [
                'label' => 'mailvotech.form.action.sendemail.setreplyto',
                'data'  => $default,
                'attr'  => [
                    'tooltip' => 'mailvotech.form.action.sendemail.setreplyto_tooltip',
                ],
            ]
        );

        $default = $options['data']['email_to_owner'] ?? false;
        $builder->add(
            'email_to_owner',
            YesNoButtonGroupType::class,
            [
                'label' => 'mailvotech.form.action.sendemail.emailtoowner',
                'data'  => $default,
            ]
        );

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
                'multiple'   => false,
            ]
        );

        $this->addToBcBccFields($builder);
    }

    public function getBlockPrefix(): string
    {
        return 'form_submitaction_sendemail';
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['formFields'] = $this->getFormFields($options['attr']['data-formid']);
    }
}
