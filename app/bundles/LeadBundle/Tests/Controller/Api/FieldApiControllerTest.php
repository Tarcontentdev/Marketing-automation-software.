<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\Controller\Api;

use Doctrine\Persistence\ManagerRegistry;
use MailVotech\ApiBundle\Helper\EntityResultHelper;
use MailVotech\CoreBundle\Factory\ModelFactory;
use MailVotech\CoreBundle\Helper\AppVersion;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\CoreBundle\Translation\Translator;
use MailVotech\LeadBundle\Controller\Api\FieldApiController;
use MailVotech\LeadBundle\Entity\LeadFieldRepository;
use MailVotech\LeadBundle\Model\FieldModel;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class FieldApiControllerTest extends TestCase
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private array $defaultWhere = [
        [
            'col'  => 'object',
            'expr' => 'eq',
            'val'  => null,
        ],
    ];

    public function testgetWhereFromRequestWithNoWhere(): void
    {
        $request = new Request();
        $result  = $this->getResultFromProtectedMethod('getWhereFromRequest', [$request], $request);

        $this->assertEquals($this->defaultWhere, $result);
    }

    public function testgetWhereFromRequestWithSomeWhere(): void
    {
        $where = [
            [
                'col'  => 'id',
                'expr' => 'eq',
                'val'  => 5,
            ],
        ];

        $request = new Request(['where' => $where]);
        $result  = $this->getResultFromProtectedMethod('getWhereFromRequest', [$request], $request);

        $this->assertEquals(array_merge($where, $this->defaultWhere), $result);
    }

    /**
     * @param array<int, mixed> $args
     */
    protected function getResultFromProtectedMethod(string $method, array $args, Request $request): mixed
    {
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')
            ->willReturn($request);

        $controller = new FieldApiController(
            $this->createStub(CorePermissions::class),
            $this->createStub(Translator::class),
            $this->createStub(EntityResultHelper::class),
            $this->createStub(Router::class),
            $this->createStub(FormFactoryInterface::class),
            $this->createStub(AppVersion::class),
            $requestStack,
            $this->createStub(ManagerRegistry::class),
            $this->createStub(ModelFactory::class),
            $this->createStub(EventDispatcherInterface::class),
            $this->createStub(CoreParametersHelper::class),
            $this->createStub(FieldModel::class),
            $this->createStub(LeadFieldRepository::class)
        );

        $controllerReflection = new \ReflectionClass(FieldApiController::class);
        $method               = $controllerReflection->getMethod($method);

        return $method->invokeArgs($controller, $args);
    }
}
