<?php

declare(strict_types=1);

namespace MailVotechPlugin\GrapesJsBuilderBundle\Tests\Unit\Model;

use Doctrine\ORM\EntityManager;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Helper\UserHelper;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\CoreBundle\Translation\Translator;
use MailVotech\EmailBundle\Entity\Email;
use MailVotech\EmailBundle\Entity\EmailRepository;
use MailVotech\EmailBundle\Model\EmailModel;
use MailVotechPlugin\GrapesJsBuilderBundle\Entity\GrapesJsBuilder;
use MailVotechPlugin\GrapesJsBuilderBundle\Entity\GrapesJsBuilderRepository;
use MailVotechPlugin\GrapesJsBuilderBundle\Model\GrapesJsBuilderModel;
use PHPUnit\Framework\Assert;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class GrapesJsBuilderModelTest extends \PHPUnit\Framework\TestCase
{
    public function testAddOrEditEntityWithoutMatchingEntityAndNoRequestQuery(): void
    {
        $requestStack = new class() extends RequestStack {
            public function __construct()
            {
            }

            public function getCurrentRequest(): Request
            {
                return new Request();
            }
        };

        $emailRepository = new class() extends EmailRepository {
            public int $saveEntityCallCount = 0;

            public function __construct()
            {
            }

            /**
             * @param object $entity
             * @param bool   $flush
             */
            public function saveEntity($entity, $flush = true): void
            {
                ++$this->saveEntityCallCount;
            }
        };

        $emailModel = $this->getEmailModel();

        $grapesJsBuilderRepository = new class() extends GrapesJsBuilderRepository {
            public int $saveEntityCallCount = 0;

            public function __construct()
            {
            }

            public function findOneBy(array $criteria, ?array $orderBy = null): ?object
            {
                return null;
            }

            /**
             * @param object $entity
             * @param bool   $flush
             */
            public function saveEntity($entity, $flush = true): void
            {
                ++$this->saveEntityCallCount;
            }
        };

        /** @phpstan-ignore class.extendsFinalByPhpDoc */
        $entityManager = new class($grapesJsBuilderRepository) extends EntityManager {
            public function __construct(
                private readonly GrapesJsBuilderRepository $grapesJsBuilderRepository,
            ) {
            }

            public function getRepository($entityName)
            {
                Assert::assertSame(GrapesJsBuilder::class, $entityName);

                return $this->grapesJsBuilderRepository; // @phpstan-ignore-line
            }
        };

        $email = new Email();

        $grapeJsBuilderModel = new GrapesJsBuilderModel(
            $entityManager,
            $this->createStub(CorePermissions::class),
            $this->createStub(EventDispatcherInterface::class),
            $this->createStub(Router::class),
            $this->getTranslator(),
            $this->createStub(UserHelper::class),
            $this->createStub(LoggerInterface::class),
            $this->createStub(CoreParametersHelper::class),
        );
        $grapeJsBuilderModel->autowireGrapesJsBuilderModel(
            $requestStack,
            $emailModel,
            $grapesJsBuilderRepository,
            $emailRepository,
        );

        $grapeJsBuilderModel->addOrEditEntity($email);

        // Not a GrapeJs email, so we are not saving anything.
        $this->assertSame(0, $grapesJsBuilderRepository->saveEntityCallCount);
        $this->assertSame(0, $emailRepository->saveEntityCallCount);
    }

    public function testAddOrEditEntityWithoutMatchingEntityAndGrapeRequestQuery(): void
    {
        $requestStack = new class() extends RequestStack {
            public function __construct()
            {
            }

            public function getCurrentRequest(): Request
            {
                return new Request(
                    [],
                    [
                        'grapesjsbuilder' => [
                            'customMjml' => '</mjml>',
                        ],
                        'emailform'       => [
                            'customHtml' => '</html>',
                        ],
                    ]
                );
            }
        };

        $emailRepository           = new class() extends EmailRepository {
            public int $saveEntityCallCount = 0;

            public function __construct()
            {
            }

            /**
             * @param Email $entity
             */
            public function saveEntity($entity, $flush = true): void
            {
                ++$this->saveEntityCallCount;

                Assert::assertSame('</html>', $entity->getCustomHtml());
            }
        };

        $emailModel = $this->getEmailModel();

        $grapesJsBuilderRepository = new class() extends GrapesJsBuilderRepository {
            public int $saveEntityCallCount = 0;

            public function __construct()
            {
            }

            public function findOneBy(array $criteria, ?array $orderBy = null): ?object
            {
                return null;
            }

            /**
             * @param GrapesJsBuilder $entity
             */
            public function saveEntity($entity, $flush = true): void
            {
                ++$this->saveEntityCallCount;

                Assert::assertSame('</mjml>', $entity->getCustomMjml());
            }
        };

        /** @phpstan-ignore class.extendsFinalByPhpDoc */
        $entityManager = new class($grapesJsBuilderRepository) extends EntityManager {
            public function __construct(
                private readonly GrapesJsBuilderRepository $grapesJsBuilderRepository,
            ) {
            }

            public function getRepository($entityName)
            {
                Assert::assertSame(GrapesJsBuilder::class, $entityName);

                return $this->grapesJsBuilderRepository; // @phpstan-ignore-line
            }
        };

        $email = new Email();

        $grapeJsBuilderModel = new GrapesJsBuilderModel(
            $entityManager,
            $this->createStub(CorePermissions::class),
            $this->createStub(EventDispatcherInterface::class),
            $this->createStub(Router::class),
            $this->getTranslator(),
            $this->createStub(UserHelper::class),
            $this->createStub(LoggerInterface::class),
            $this->createStub(CoreParametersHelper::class),
        );
        $grapeJsBuilderModel->autowireGrapesJsBuilderModel(
            $requestStack,
            $emailModel,
            $grapesJsBuilderRepository,
            $emailRepository,
        );

        $grapeJsBuilderModel->addOrEditEntity($email);

        // Saving the entities now.
        $this->assertSame(1, $grapesJsBuilderRepository->saveEntityCallCount);
        $this->assertSame(1, $emailRepository->saveEntityCallCount);
    }

    private function getEmailModel(): EmailModel
    {
        return new class() extends EmailModel {
            public function __construct()
            {
            }
        };
    }

    private function getTranslator(): Translator
    {
        return new class() extends Translator {
            public function __construct()
            {
            }
        };
    }
}
