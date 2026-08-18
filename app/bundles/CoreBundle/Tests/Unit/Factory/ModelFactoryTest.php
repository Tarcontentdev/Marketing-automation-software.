<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Tests\Unit\Factory;

use MailVotech\CoreBundle\Factory\ModelFactory;
use MailVotech\PointBundle\Model\TriggerModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;

final class ModelFactoryTest extends TestCase
{
    /**
     * @var MockObject&ServiceLocator
     */
    private MockObject $container;

    /**
     * @var ModelFactory<object>
     */
    private ModelFactory $factory;

    protected function setUp(): void
    {
        $this->container = $this->createMock(ServiceLocator::class);
        $this->factory   = new ModelFactory($this->container);
    }

    public function testModelKeyIsLowerCaseToMatchServiceKeys(): void
    {
        $pointTriggerModel = $this->createStub(TriggerModel::class);
        $modelName         = 'point.triggerEvent';
        $containerKey      = 'mailvotech.point.model.triggerEvent';

        $this->container->expects($this->once())
            ->method('has')
            ->with($containerKey)
            ->willReturn(true);

        $this->container->expects($this->once())
            ->method('get')
            ->with($containerKey)
            ->willReturn($pointTriggerModel);

        $givenPointTriggerModel = $this->factory->getModel($modelName);

        $this->assertInstanceOf(TriggerModel::class, $givenPointTriggerModel);
    }
}
