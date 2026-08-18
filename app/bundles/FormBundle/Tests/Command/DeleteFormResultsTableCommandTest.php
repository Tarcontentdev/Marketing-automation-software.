<?php

declare(strict_types=1);

namespace MailVotech\FormBundle\Tests\Command;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\FormBundle\Entity\Form;
use MailVotech\FormBundle\Entity\FormRepository;
use MailVotech\FormBundle\Entity\Submission;
use MailVotech\FormBundle\Entity\SubmissionRepository;
use MailVotech\FormBundle\Tests\FormTestHelperTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

final class DeleteFormResultsTableCommandTest extends MailVotechMysqlTestCase
{
    use FormTestHelperTrait;

    protected $useCleanupRollback = false;

    public function testResultsTableAreDeletedIfFormsAreRemovedFromTable(): void
    {
        // In case any form results table exists whose form is deleted in previous test cases
        $this->deleteAllFormResultsTable();

        /** @var SubmissionRepository $submissionRepository */
        $submissionRepository = $this->em->getRepository(Submission::class);

        /** @var FormRepository $formRepository */
        $formRepository = $this->em->getRepository(Form::class);

        $deletedForms = 20;

        for ($i = 0; $i < $deletedForms; ++$i) {
            $payload = $this->getPayLoad();

            $form = $this->createFormViaApi($payload);

            $this->submitForm($form);

            $deleteForm = $formRepository->findOneBy(['id' => $form['id']]);
            $formRepository->deleteEntity($deleteForm);

            $deletedForm = $formRepository->findBy(['id' => $form['id']]);

            $this->assertCount(0, $deletedForm);

            $submissions = $submissionRepository->findBy(['form' => $form['id']]);

            $this->assertCount(0, $submissions);
        }

        $kernel      = self::$kernel;
        $application = new Application($kernel);
        $application->setAutoExit(false);
        $command = $application->find('mailvotech:forms:delete-results-table');

        $commandTester = new CommandTester($command);
        $this->em->clear();

        $commandTester->execute([]);

        $this->assertSame(0, $commandTester->getStatusCode());
        $outputMessage = $commandTester->getDisplay();
        $message       = "Dropped {$deletedForms} form results table whose forms have been deleted";

        $this->assertStringContainsString($message, $outputMessage);
    }
}
