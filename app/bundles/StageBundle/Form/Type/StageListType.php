<?php

declare(strict_types=1);

namespace MailVotech\StageBundle\Form\Type;

use MailVotech\StageBundle\Entity\Stage;
use MailVotech\StageBundle\Entity\StageRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Stage>
 */
final class StageListType extends AbstractType
{
    /**
     * @var array<string,int>
     */
    private array $choices = [];

    public function __construct(
        private readonly StageRepository $stageRepository,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices'           => $this->getStageChoices(),
            'expanded'          => false,
            'multiple'          => true,
            'required'          => false,
            'placeholder'       => 'mailvotech.core.form.chooseone',
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    /**
     * @return array<string,int>
     */
    private function getStageChoices(): array
    {
        if ($this->choices) {
            return $this->choices;
        }

        $stages = $this->stageRepository->getEntities([
            'filter' => [
                'force' => [
                    [
                        'column' => 's.isPublished',
                        'expr'   => 'eq',
                        'value'  => true,
                    ],
                ],
            ],
        ]);

        /** @var Stage $stage */
        foreach ($stages as $stage) {
            $this->choices[$stage->getName()] = $stage->getId();
        }

        // sort by language
        ksort($this->choices);

        return $this->choices;
    }
}
