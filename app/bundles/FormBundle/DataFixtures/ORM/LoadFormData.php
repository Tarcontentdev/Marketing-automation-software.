<?php

namespace MailVotech\FormBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use MailVotech\CoreBundle\Doctrine\Common\DataFixtures\Event\PreExecuteEvent;
use MailVotech\CoreBundle\Helper\CsvHelper;
use MailVotech\CoreBundle\Helper\Serializer;
use MailVotech\FormBundle\Entity\Action;
use MailVotech\FormBundle\Entity\ActionRepository;
use MailVotech\FormBundle\Entity\Field;
use MailVotech\FormBundle\Entity\FieldRepository;
use MailVotech\FormBundle\Entity\Form;
use MailVotech\FormBundle\Entity\FormRepository;
use MailVotech\FormBundle\Model\FormModel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class LoadFormData extends AbstractFixture implements OrderedFixtureInterface
{
    public const FORM_PREFIX = 'form-';

    /**
     * @var array<int, Form>
     */
    private array $formEntities = [];

    /**
     * @var array<int, Field>
     */
    private array $fieldEntities = [];

    /**
     * @var array<int, Action>
     */
    private array $actionEntities = [];

    public function __construct(
        private readonly FormModel $formModel,
        EventDispatcherInterface $eventDispatcher,
        private readonly FormRepository $formRepository,
        private readonly FieldRepository $fieldRepository,
        private readonly ActionRepository $actionRepository,
    ) {
        // this will load the data before fixtures are loaded
        $eventDispatcher->addListener(PreExecuteEvent::class, function (PreExecuteEvent $event): void {
            $formEntities = $this->getFormEntities();
            $this->getFieldEntities();
            $this->getActionEntities();
            $firstId = 0;

            // create the tables passed in LoadFormData fixture.
            foreach ($formEntities as $form) {
                $this->formModel->generateHtml($form);

                if ($form->getId() < 1) {
                    // Method above saves the form entity. If this exception is thrown all you need to do is to save an entity.
                    throw new \RuntimeException('Form must have an ID set.');
                }

                if (0 === $firstId) {
                    $firstId = $form->getId();
                }

                $this->formModel->createTableSchema($form, true, true);
            }

            if ($event->isTruncate()) {
                return;
            }

            // need to delete created form entities, because executor will wrap DELETE query into transaction and
            // will insert form entries with new autoincrement.
            foreach ($formEntities as $formEntity) {
                $this->formModel->deleteEntity($formEntity);
            }

            // because form table data will be deleted we must have same autoincrement as before the insertion
            // to have the form_results table to match the form id in table name e.g. form_results_69_kaleidosco
            $formTableName = $this->formRepository->getTableName();
            $event->getEntityManager()->getConnection()->executeStatement(
                'ALTER TABLE '.$formTableName.' AUTO_INCREMENT='.$firstId
            );
        });
    }

    public function load(ObjectManager $manager): void
    {
        $this->getFormEntities();
        $this->getFieldEntities();
        $this->getActionEntities();

        foreach ($this->formEntities as $key => $formEntity) {
            $this->formRepository->saveEntity($formEntity);
            $this->setReference(self::FORM_PREFIX.$key, $formEntity);
        }

        foreach ($this->fieldEntities as $field) {
            $this->fieldRepository->saveEntity($field);
        }

        foreach ($this->actionEntities as $action) {
            $this->actionRepository->saveEntity($action);
        }
    }

    public function getOrder(): int
    {
        return 8;
    }

    /**
     * @return array<int, Form>
     */
    private function getFormEntities(): array
    {
        $forms              = CsvHelper::csv_to_array(__DIR__.'/fakeformdata.csv');
        $this->formEntities = [];
        foreach ($forms as $count => $rows) {
            $form = new Form();
            $key  = $count + 1;
            foreach ($rows as $col => $val) {
                if ('NULL' !== $val) {
                    $setter = 'set'.ucfirst($col);

                    if ('dateAdded' === $col) {
                        $form->setDateAdded(new \DateTime($val));
                    } elseif ('cachedHtml' === $col) {
                        $val = stripslashes($val);
                        $form->setCachedHtml($val);
                    } else {
                        $form->{$setter}($val);
                    }
                }
            }
            $this->formEntities[$key] = $form;
        }

        return $this->formEntities;
    }

    private function getFieldEntities(): void
    {
        if (0 === count($this->formEntities)) {
            throw new \RuntimeException('This method must be called after getFormEntities.');
        }

        $this->fieldEntities = [];
        $fields              = CsvHelper::csv_to_array(__DIR__.'/fakefielddata.csv');
        foreach ($fields as $count => $rows) {
            $field = new Field();
            foreach ($rows as $col => $val) {
                if ('NULL' !== $val) {
                    $setter = 'set'.ucfirst($col);

                    if ('form' === $col) {
                        $form = $this->formEntities[$val];
                        $field->setForm($form);
                        $form->addField($count, $field);
                    } elseif (in_array($col, ['customParameters', 'properties'], true)) {
                        $val = Serializer::decode(stripslashes($val));
                        $field->{$setter}($val);
                    } else {
                        $field->{$setter}($val);
                    }
                }
            }
            $this->fieldEntities[$count] = $field;
        }
    }

    private function getActionEntities(): void
    {
        if (0 === count($this->formEntities)) {
            throw new \RuntimeException('This method must be called after getFormEntities.');
        }

        $this->actionEntities = [];
        $actions              = CsvHelper::csv_to_array(__DIR__.'/fakeactiondata.csv');
        foreach ($actions as $rows) {
            $action = new Action();
            foreach ($rows as $col => $val) {
                if ('NULL' !== $val) {
                    $setter = 'set'.ucfirst($col);

                    if ('form' === $col) {
                        $action->setForm($this->formEntities[$val]);
                    } elseif ('properties' === $col) {
                        $val = Serializer::decode(stripslashes($val));
                        $action->setProperties($val);
                    } else {
                        $action->{$setter}($val);
                    }
                }
            }

            $this->actionEntities[] = $action;
        }
    }
}
