<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\Command;

use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Helper\ExitCode;
use MailVotech\CoreBundle\ProcessSignal\Exception\SignalCaughtException;
use MailVotech\CoreBundle\ProcessSignal\ProcessSignalService;
use MailVotech\CoreBundle\Twig\Helper\DateHelper;
use MailVotech\CoreBundle\Twig\Helper\FormatterHelper;
use MailVotech\LeadBundle\Command\ContactScheduledExportCommand;
use MailVotech\LeadBundle\Entity\ContactExportScheduler;
use MailVotech\LeadBundle\Entity\ContactExportSchedulerRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ContactScheduledExportCommandTest extends TestCase
{
    public function testForSignalCaughtException(): void
    {
        $eventDispatcher             = $this->createMock(EventDispatcherInterface::class);

        $translator           = $this->createStub(TranslatorInterface::class);
        $coreParametersHelper = $this->createStub(CoreParametersHelper::class);
        $dateHelper           = new DateHelper(
            'F j, Y g:i a T',
            'D, M d',
            'F j, Y',
            'g:i a',
            $translator,
            $coreParametersHelper
        );

        $formatterHelper             = new FormatterHelper($dateHelper, $translator);
        $processSignalService        = $this->createStub(ProcessSignalService::class);

        $contactExportSchedulerRepository = $this->createMock(ContactExportSchedulerRepository::class);
        $contactExportSchedulerRepository->method('findBy')
            ->willReturn([new ContactExportScheduler()]);

        $eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->willThrowException(new SignalCaughtException(1));

        $command = new class($eventDispatcher, $formatterHelper, $processSignalService, $contactExportSchedulerRepository) extends ContactScheduledExportCommand {
            public function getExecute(InputInterface $input, OutputInterface $output): int
            {
                return $this->execute($input, $output);
            }
        };

        $inputInterfaceMock  = $this->createMock(InputInterface::class);
        $outputInterfaceMock = $this->createStub(OutputInterface::class);

        $inputInterfaceMock->method('getOption')
            ->with('ids')
            ->willReturn(1);

        $this->assertSame(ExitCode::TERMINATED, $command->getExecute($inputInterfaceMock, $outputInterfaceMock));
    }
}
