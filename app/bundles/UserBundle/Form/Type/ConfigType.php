<?php

namespace MailVotech\UserBundle\Form\Type;

use MailVotech\ConfigBundle\Form\Type\ConfigFileType;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractType<array<mixed>>
 */
final class ConfigType extends AbstractType
{
    public function __construct(
        private readonly CoreParametersHelper $parameters,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $samlEntityIdChoices = ['', rtrim($this->parameters->get('mailvotech.site_url'), '/')];
        if (!empty($this->parameters->get('mailvotech.subdomain_url'))) {
            $samlEntityIdChoices[] = rtrim($this->parameters->get('mailvotech.subdomain_url'), '/');
        }
        $builder->add('saml_idp_entity_id', ChoiceType::class,
            [
                'choices'    => array_combine($samlEntityIdChoices, $samlEntityIdChoices),
                'label'      => 'mailvotech.user.config.form.saml.idp_entity_id_label',
                'label_attr' => ['class' => 'control-label'],
                'required'   => true,
                'multiple'   => false,
                'attr'       => [
                    'class' => 'form-control',
                ],
            ]);

        $builder->add(
            'saml_idp_metadata',
            ConfigFileType::class,
            [
                'label'      => 'mailvotech.user.config.form.saml.idp.metadata',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'mailvotech.user.config.form.saml.idp.metadata.tooltip',
                    'rows'    => 10,
                ],
                'required'    => false,
                'constraints' => [
                    new File(mimeTypes: ['text/plain', 'text/xml', 'application/xml'], mimeTypesMessage: 'mailvotech.core.invalid_file_type'),
                ],
            ]
        );

        $builder->add(
            'saml_idp_own_certificate',
            ConfigFileType::class,
            [
                'label'      => 'mailvotech.user.config.form.saml.idp.own_certificate',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'mailvotech.user.config.form.saml.idp.own_certificate.tooltip',
                ],
                'required'    => false,
                'constraints' => [
                    new File(mimeTypes: ['text/plain'], mimeTypesMessage: 'mailvotech.core.invalid_file_type'),
                ],
            ]
        );

        $builder->add(
            'saml_idp_own_private_key',
            ConfigFileType::class,
            [
                'label'      => 'mailvotech.user.config.form.saml.idp.own_private_key',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'mailvotech.user.config.form.saml.idp.own_private_key.tooltip',
                ],
                'required'    => false,
                'constraints' => [
                    new File(mimeTypes: ['text/plain'], mimeTypesMessage: 'mailvotech.core.invalid_file_type'),
                ],
            ]
        );

        $builder->add(
            'saml_idp_own_password',
            PasswordType::class,
            [
                'label'      => 'mailvotech.user.config.form.saml.idp.own_password',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'mailvotech.user.config.form.saml.idp.own_password.tooltip',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'saml_idp_email_attribute',
            TextType::class,
            [
                'label'      => 'mailvotech.user.config.form.saml.idp.attribute_email',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class' => 'form-control',
                ],
                'empty_data' => 'EmailAddress',
            ]
        );

        $builder->add(
            'saml_idp_username_attribute',
            TextType::class,
            [
                'label'      => 'mailvotech.user.config.form.saml.idp.attribute_username',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class' => 'form-control',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'saml_idp_firstname_attribute',
            TextType::class,
            [
                'label'      => 'mailvotech.user.config.form.saml.idp.attribute_firstname',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class' => 'form-control',
                ],
                'empty_data' => 'FirstName',
            ]
        );

        $builder->add(
            'saml_idp_lastname_attribute',
            TextType::class,
            [
                'label'      => 'mailvotech.user.config.form.saml.idp.attribute_lastname',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class' => 'form-control',
                ],
                'empty_data' => 'LastName',
            ]
        );

        $builder->add(
            'saml_idp_default_role',
            RoleListType::class,
            [
                'label'      => 'mailvotech.user.config.form.saml.idp.default_role',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'            => 'form-control',
                    'data-placeholder' => $this->translator->trans('mailvotech.user.config.form.saml.idp.disable_creation'),
                    'tooltip'          => 'mailvotech.user.config.form.saml.idp.default_role.tooltip',
                ],
                'required'    => false,
                'placeholder' => '',
            ]
        );
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['entityId'] = $this->parameters->get('mailvotech.saml_idp_entity_id');
    }

    public function getBlockPrefix(): string
    {
        return 'userconfig';
    }
}
