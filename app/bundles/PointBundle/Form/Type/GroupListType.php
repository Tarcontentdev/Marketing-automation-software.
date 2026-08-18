<?php

namespace MailVotech\PointBundle\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use MailVotech\CoreBundle\Form\DataTransformer\IdToEntityModelTransformer;
use MailVotech\PointBundle\Entity\Group;
use MailVotech\PointBundle\Entity\GroupRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<GroupListType>
 */
final class GroupListType extends AbstractType
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly GroupRepository $repo,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (true === $options['return_entity']) {
            $transformer = new IdToEntityModelTransformer($this->em, Group::class, 'id');
            $builder->addModelTransformer($transformer);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => function (Options $options): array {
                $groups  = $this->repo->getEntities();
                $choices = [];
                foreach ($groups as $l) {
                    $choices[$l->getName().' ('.$l->getId().')'] = $l->getId();
                }

                return $choices;
            },
            'label'             => 'mailvotech.point.group.form.group',
            'label_attr'        => ['class' => 'control-label'],
            'multiple'          => false,
            'required'          => false,
            'return_entity'     => true,
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
