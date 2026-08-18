<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechFocusBundle\Tests\Model;

use Doctrine\ORM\EntityManagerInterface;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Helper\UserHelper;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\CoreBundle\Translation\Translator;
use MailVotech\FormBundle\Model\FormModel;
use MailVotech\LeadBundle\Model\FieldModel;
use MailVotech\LeadBundle\Tracker\ContactTracker;
use MailVotech\PageBundle\Model\TrackableModel;
use MailVotechPlugin\MailVotechFocusBundle\Entity\FocusRepository;
use MailVotechPlugin\MailVotechFocusBundle\Entity\StatRepository;
use MailVotechPlugin\MailVotechFocusBundle\Model\FocusModel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvokedCount;
use PHPUnit\Framework\MockObject\Rule\InvokedCount as InvokedCountMatcher;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

final class FocusModelTest extends TestCase
{
    /**
     * @var MockObject&FormModel
     */
    private MockObject $formModel;

    protected function setUp(): void
    {
        $this->formModel      = $this->createMock(FormModel::class);
        parent::setUp();
    }

    #[DataProvider('focusTypeProvider')]
    public function testGetContentWithForm(string $type, InvokedCount $count): void
    {
        $this->formModel->expects($this->once())->method('getPages')->willReturn(['', '']);

        $this->formModel->expects($count)->method('getEntity');

        $focusModel = new FocusModel(
            $this->formModel,
            $this->createStub(TrackableModel::class),
            $this->createStub(Environment::class),
            $this->createStub(FieldModel::class),
            $this->createStub(ContactTracker::class),
            $this->createStub(EntityManagerInterface::class),
            $this->createStub(CorePermissions::class),
            $this->createStub(EventDispatcherInterface::class),
            $this->createStub(UrlGeneratorInterface::class),
            $this->createStub(Translator::class),
            $this->createStub(UserHelper::class),
            $this->createStub(LoggerInterface::class),
            $this->createStub(CoreParametersHelper::class),
            $this->createStub(FocusRepository::class), // $focusRepository
            $this->createStub(StatRepository::class), // $statRepository
        );
        $focus = [
            'form' => 'xxx',
            'type' => $type,
        ];

        $focusModel->getContent($focus);
    }

    public static function focusTypeProvider(): \Generator
    {
        yield ['form', new InvokedCountMatcher(1)];
        yield ['notice', new InvokedCountMatcher(0)];
    }
}
