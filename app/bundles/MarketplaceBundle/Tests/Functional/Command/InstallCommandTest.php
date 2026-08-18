<?php

declare(strict_types=1);

namespace MailVotech\MarketplaceBundle\Tests\Functional\Command;

use MailVotech\CoreBundle\Helper\ComposerHelper;
use MailVotech\CoreBundle\Test\AbstractMailVotechTestCase;
use MailVotech\MarketplaceBundle\Command\InstallCommand;
use MailVotech\MarketplaceBundle\DTO\ConsoleOutput;
use MailVotech\MarketplaceBundle\DTO\PackageDetail;
use MailVotech\MarketplaceBundle\Exception\ApiException;
use MailVotech\MarketplaceBundle\Model\PackageModel;
use PHPUnit\Framework\MockObject\MockObject;

final class InstallCommandTest extends AbstractMailVotechTestCase
{
    /**
     * @var MockObject&ComposerHelper
     */
    private MockObject $composerHelper;

    /**
     * @var MockObject&PackageModel
     */
    private MockObject $packageModel;

    private string $packageName;

    protected function setUp(): void
    {
        parent::setUp();
        $this->composerHelper = $this->createMock(ComposerHelper::class);
        $this->packageModel   = $this->createMock(PackageModel::class);
        $this->packageName    = 'koco/mailvotech-recaptcha-bundle';
    }

    public function testInstallCommand(): void
    {
        $this->packageModel->method('getPackageDetail')
            ->with($this->packageName)
            ->willReturn($this->getPackageDetail());

        $this->composerHelper->method('install')
            ->with($this->packageName)
            ->willReturn(new ConsoleOutput(0, 'OK'));

        $command = new InstallCommand($this->composerHelper, $this->packageModel);

        $result = $this->testSymfonyCommand(
            'mailvotech:marketplace:install',
            ['package' => $this->packageName],
            $command
        );

        $this->assertSame(0, $result->getStatusCode());
    }

    public function testInstallCommandWithDryRun(): void
    {
        $this->packageModel->method('getPackageDetail')
            ->with($this->packageName)
            ->willReturn($this->getPackageDetail());

        $this->composerHelper->method('install')
            ->with($this->packageName)
            ->willReturn(new ConsoleOutput(0, 'OK'));

        $command = new InstallCommand($this->composerHelper, $this->packageModel);

        $result = $this->testSymfonyCommand(
            'mailvotech:marketplace:install',
            ['package' => $this->packageName, '--dry-run' => null],
            $command
        );

        $this->assertSame(0, $result->getStatusCode());
        $this->assertStringContainsString('dry-running this installation', $result->getDisplay());
    }

    public function testInstallCommandWithNonExistingPackage(): void
    {
        $packageName = 'mailvotech/non-existent-plugin';

        $this->packageModel->method('getPackageDetail')
            ->with($packageName)
            ->willThrowException(new ApiException('Package not found', 404));

        $command = new InstallCommand($this->composerHelper, $this->packageModel);

        $this->expectException(\InvalidArgumentException::class);

        $this->testSymfonyCommand(
            'mailvotech:marketplace:install',
            ['package' => $packageName],
            $command
        );
    }

    public function testInstallCommandWithComposerNotAvailable(): void
    {
        $packageName = 'mailvotech/non-existent-plugin';

        $this->packageModel->method('getPackageDetail')
            ->with($packageName)
            ->willThrowException(new ApiException('Internal Server Error', 500));

        $command = new InstallCommand($this->composerHelper, $this->packageModel);

        $this->expectException(\Exception::class);

        $this->testSymfonyCommand(
            'mailvotech:marketplace:install',
            ['package' => $packageName],
            $command
        );
    }

    public function testInstallCommandWithWrongPackageType(): void
    {
        $packageName                      = 'mailvotech/package-with-wrong-type';
        $packageDetail                    = $this->getPackageDetail();
        $packageDetail->packageBase->type = 'non-existent-type';

        $this->packageModel->method('getPackageDetail')
            ->with($packageName)
            ->willReturn($packageDetail);

        $command = new InstallCommand($this->composerHelper, $this->packageModel);

        $this->expectException(\Exception::class);

        $this->testSymfonyCommand(
            'mailvotech:marketplace:install',
            ['package' => $packageName],
            $command
        );
    }

    public function testInstallCommandWithFailedComposerCommand(): void
    {
        $packageName = 'mailvotech/crash-package';

        $this->composerHelper->method('install')
            ->with($packageName)
            ->willReturn(new ConsoleOutput(1, 'Something went wrong during the installation'));

        $this->packageModel->method('getPackageDetail')
            ->with($packageName)
            ->willReturn($this->getPackageDetail());

        $command = new InstallCommand($this->composerHelper, $this->packageModel);
        $result  = $this->testSymfonyCommand(
            'mailvotech:marketplace:install',
            ['package' => $packageName],
            $command
        );

        $this->assertSame(1, $result->getStatusCode());
        $this->assertSame("Installing mailvotech/crash-package, this might take a while...\nError while installing this plugin.\nSomething went wrong during the installation\n", $result->getDisplay());
    }

    private function getPackageDetail(): PackageDetail
    {
        $payload = json_decode(file_get_contents(__DIR__.'/../../ApiResponse/detail.json'), true);

        return PackageDetail::fromArray($payload['package']);
    }
}
