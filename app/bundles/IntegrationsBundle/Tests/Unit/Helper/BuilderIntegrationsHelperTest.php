<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Tests\Unit\Helper;

use MailVotech\IntegrationsBundle\Exception\IntegrationNotFoundException;
use MailVotech\IntegrationsBundle\Helper\BuilderIntegrationsHelper;
use MailVotech\IntegrationsBundle\Helper\IntegrationsHelper;
use MailVotech\IntegrationsBundle\Integration\Interfaces\BuilderInterface;
use MailVotech\PluginBundle\Entity\Integration;
use PHPUnit\Framework\TestCase;

final class BuilderIntegrationsHelperTest extends TestCase
{
    public function testBuilderNotFoundIfFeatureSupportedButNotEnabled(): void
    {
        $builder     = $this->createMock(BuilderInterface::class);
        $integration = new Integration();

        $builder->expects($this->once())
            ->method('isSupported')
            ->with('page')
            ->willReturn(true);

        $builder->expects($this->once())
            ->method('getIntegrationConfiguration')
            ->willReturn($integration);

        $builderIntegrationsHelper = $this->createBuilderIntegrationsHelper($builder);

        $this->expectException(IntegrationNotFoundException::class);

        $builderIntegrationsHelper->getBuilder('page');
    }

    public function testBuilderNotFoundIfFeatureIsNotSupported(): void
    {
        $builder = $this->createMock(BuilderInterface::class);
        $builder->expects($this->once())
            ->method('isSupported')
            ->with('page')
            ->willReturn(false);

        $builder->expects($this->never())
            ->method('getIntegrationConfiguration');

        $builderIntegrationsHelper = $this->createBuilderIntegrationsHelper($builder);

        $this->expectException(IntegrationNotFoundException::class);

        $builderIntegrationsHelper->getBuilder('page');
    }

    public function testBuilderFoundIfFeatureIsSupportedAndBuilderEnabled(): void
    {
        $builder = $this->createMock(BuilderInterface::class);

        $integration = new Integration();
        $integration->setIsPublished(true);

        $builder->expects($this->once())
            ->method('isSupported')
            ->with('page')
            ->willReturn(true);

        $builder->expects($this->once())
            ->method('getIntegrationConfiguration')
            ->willReturn($integration);

        $builderIntegrationsHelper = $this->createBuilderIntegrationsHelper($builder);

        $foundBuilder = $builderIntegrationsHelper->getBuilder('page');

        $this->assertSame($builder, $foundBuilder);
    }

    public function testBuilderNamesAreReturned(): void
    {
        $builder1 = $this->createMock(BuilderInterface::class);
        $builder1->expects($this->exactly(2))
            ->method('getName')
            ->willReturn('builder1');
        $builder1->expects($this->once())
            ->method('getDisplayName')
            ->willReturn('Builder One');

        $builder2 = $this->createMock(BuilderInterface::class);
        $builder2->expects($this->exactly(2))
            ->method('getName')
            ->willReturn('builder2');
        $builder2->expects($this->once())
            ->method('getDisplayName')
            ->willReturn('Builder Two');

        $builderIntegrationsHelper = $this->createBuilderIntegrationsHelper($builder1, $builder2);

        $this->assertSame([
            'builder1' => 'Builder One',
            'builder2' => 'Builder Two',
        ], $builderIntegrationsHelper->getBuilderNames());
    }

    private function createBuilderIntegrationsHelper(BuilderInterface ...$builders): BuilderIntegrationsHelper
    {
        return new BuilderIntegrationsHelper($this->createStub(IntegrationsHelper::class), $builders);
    }
}
