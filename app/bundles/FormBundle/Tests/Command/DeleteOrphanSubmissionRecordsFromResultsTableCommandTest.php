<?php

declare(strict_types=1);

namespace MailVotech\FormBundle\Tests\Command;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\FormBundle\Entity\Submission;
use MailVotech\FormBundle\Entity\SubmissionRepository;
use MailVotech\FormBundle\Tests\FormTestHelperTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

final class DeleteOrphanSubmissionRecordsFromResultsTableCommandTest extends MailVotechMysqlTestCase
{
    use FormTestHelperTrait;

    protected $useCleanupRollback = false;

    public function testResultsAreRemovedFromFormResultsTableIfItsSubmissionRecordIsNotFound(): void
    {
        $payload = $this->getPayLoad();

        $form = $this->createFormViaApi($payload);

        $timesFormSubmitted = 100;

        for ($i = 0; $i < $timesFormSubmitted; ++$i) {
            $this->submitForm($form);
        }

        /** @var SubmissionRepository $submissionRepository */
        $submissionRepository = $this->em->getRepository(Submission::class);

        // Ensure the submission was created properly.
        $submissions = $submissionRepository->findBy(['form' => $form['id']]);

        $this->assertCount($timesFormSubmitted, $submissions);

        foreach ($submissions as $submission) {
            $submissionRepository->deleteEntity($submission);
        }

        $submissions = $submissionRepository->findBy(['form' => $form['id']]);

        $this->assertCount(0, $submissions);

        $kernel      = self::$kernel;
        $application = new Application($kernel);
        $application->setAutoExit(false);
        $command       = $application->find('mailvotech:forms:delete-orphan-form-submission-records-from-form-results-table');

        $commandTester = new CommandTester($command);
        $this->em->clear();

        $commandTester->execute([]);

        $this->assertSame(0, $commandTester->getStatusCode());
        $outputMessage = $commandTester->getDisplay();
        $this->assertStringContainsString("Total Removed Records from form results table = {$timesFormSubmitted}", $outputMessage);
    }
}
