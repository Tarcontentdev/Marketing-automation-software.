<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @extends AbstractType<mixed>
 */
final class AddToCompanyActionType extends AbstractType
{
    public function __construct(
        private readonly RouterInterface $router,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'company',
            CompanyListType::class,
            [
                'multiple'    => false,
                'required'    => true,
                'modal_route' => false,
                'constraints' => [
                    new NotBlank(
                        message: 'mailvotech.company.choosecompany.notblank'
                    ),
                ],
            ]
        );

        $windowUrl = $this->router->generate(
            'mailvotech_company_action',
            [
                'objectAction' => 'new',
                'contentOnly'  => 1,
                'updateSelect' => 'campaignevent_properties_company',
            ]
        );

        $builder->add(
            'newCompanyButton',
            ButtonType::class,
            [
                'attr' => [
                    'class'   => 'btn btn-primary btn-nospin',
                    'onclick' => 'MailVotech.loadNewWindow({"windowUrl": "'.$windowUrl.'"})',
                    'icon'    => 'ri-add-line',
                ],
                'label' => 'mailvotech.company.new.company',
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'addtocompany_action';
    }
}
