<?php

namespace MailVotech\CategoryBundle\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use MailVotech\CategoryBundle\Entity\Category;
use MailVotech\CategoryBundle\Model\CategoryModel;
use MailVotech\CoreBundle\Form\DataTransformer\IdToEntityModelTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractType<mixed>
 */
final class CategoryListType extends AbstractType
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator,
        private readonly CategoryModel $model,
        private readonly RouterInterface $router,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (true === $options['return_entity']) {
            $transformer = new IdToEntityModelTransformer($this->em, Category::class, 'id');
            $builder->addModelTransformer($transformer);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => function (Options $options): array {
                $createNew  = $this->translator->trans('mailvotech.category.createnew');
                $categories = $this->model->getLookupResults($options['bundle'], '', 0);
                $choices    = [];
                foreach ($categories as $l) {
                    $choices[$l['title'].' ('.$l['id'].')'] = $l['id'];
                }
                $choices[$createNew] = 'new';

                return $choices;
            },
            'label'             => 'mailvotech.core.category',
            'label_attr'        => ['class' => 'control-label'],
            'multiple'          => false,
            'placeholder'       => 'mailvotech.core.form.uncategorized',
            'attr'              => function (Options $options): array {
                $modalHeader = $this->translator->trans('mailvotech.category.header.new');
                $newUrl      = $this->router->generate('mailvotech_category_action', [
                    'objectAction' => 'new',
                    'bundle'       => $options['bundle'],
                    'inForm'       => 1,
                ]);

                return [
                    'class'    => 'form-control category-select',
                    'onchange' => "MailVotech.loadAjaxModalBySelectValue(this, 'new', '{$newUrl}', '{$modalHeader}');",
                ];
            },
            'required'      => false,
            'return_entity' => true,
        ]);

        $resolver->setRequired(['bundle']);
    }

    public function getBlockPrefix(): string
    {
        return 'category';
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
