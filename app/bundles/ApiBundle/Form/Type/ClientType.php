<?php

namespace MailVotech\ApiBundle\Form\Type;

use MailVotech\ApiBundle\Entity\oAuth2\Client;
use MailVotech\ApiBundle\Form\Validator\Constraints\OAuthCallback;
use MailVotech\CoreBundle\Form\DataTransformer as Transformers;
use MailVotech\CoreBundle\Form\EventListener\CleanFormSubscriber;
use MailVotech\CoreBundle\Form\EventListener\FormExitSubscriber;
use MailVotech\CoreBundle\Form\Type\FormButtonsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractType<Client>
 */
final class ClientType extends AbstractType
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly TranslatorInterface $translator,
        private readonly ValidatorInterface $validator,
        private readonly RouterInterface $router,
    ) {
    }

    /**
     * @return bool|mixed
     */
    private function getApiMode()
    {
        return $this->requestStack->getCurrentRequest()->get(
            'api_mode',
            $this->requestStack->getSession()->get('mailvotech.client.filter.api_mode', 'oauth2')
        );
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $apiMode = $this->getApiMode();
        $builder->addEventSubscriber(new CleanFormSubscriber([]));
        $builder->addEventSubscriber(new FormExitSubscriber('api.client', $options));

        if (!$options['data']->getId()) {
            $builder->add(
                'api_mode',
                ChoiceType::class,
                [
                    'mapped'     => false,
                    'label'      => 'mailvotech.api.client.form.auth_protocol',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'    => 'form-control',
                        'onchange' => 'MailVotech.refreshApiClientForm(\''.$this->router->generate('mailvotech_client_action', ['objectAction' => 'new']).'\', this)',
                    ],
                    'choices' => [
                        'OAuth 2'    => 'oauth2',
                    ],
                    'required'          => false,
                    'placeholder'       => false,
                    'data'              => $apiMode,
                ]
            );
        }

        $builder->add(
            'name',
            TextType::class,
            [
                'label'      => 'mailvotech.core.name',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
            ]
        );

        $arrayStringTransformer = new Transformers\ArrayStringTransformer();
        $builder->add(
            $builder->create(
                'redirectUris',
                TextType::class,
                [
                    'label'      => 'mailvotech.api.client.redirecturis',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'   => 'form-control',
                        'tooltip' => 'mailvotech.api.client.form.help.requesturis',
                    ],
                ]
            )
                ->addViewTransformer($arrayStringTransformer)
        );

        $builder->add(
            'publicId',
            TextType::class,
            [
                'label'      => 'mailvotech.api.client.form.clientid',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
                'disabled'   => true,
                'required'   => false,
                'mapped'     => false,
                'data'       => $options['data']->getPublicId(),
            ]
        );

        $builder->add(
            'secret',
            TextType::class,
            [
                'label'      => 'mailvotech.api.client.form.clientsecret',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
                'disabled'   => true,
                'required'   => false,
            ]
        );

        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event): void {
                $form = $event->getForm();
                $data = $event->getData();

                if ($form->has('redirectUris')) {
                    foreach ($data->getRedirectUris() as $uri) {
                        $urlConstraint          = new OAuthCallback();
                        $urlConstraint->message = $this->translator->trans(
                            'mailvotech.api.client.redirecturl.invalid',
                            ['%url%' => $uri],
                            'validators'
                        );

                        $errors = $this->validator->validate($uri, $urlConstraint);

                        foreach ($errors as $error) {
                            $form['redirectUris']->addError(new FormError($error->getMessage()));
                        }
                    }
                }
            }
        );

        $builder->add('buttons', FormButtonsType::class);

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $dataClass = Client::class;
        $resolver->setDefaults(
            [
                'data_class' => $dataClass,
            ]
        );
    }
}
