<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\Tests\Form\Type;

use Doctrine\ORM\EntityManager;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Helper\ThemeHelperInterface;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\EmailBundle\Entity\Email;
use MailVotech\EmailBundle\Form\Type\EmailType;
use MailVotech\EmailBundle\Helper\EmailConfigInterface;
use MailVotech\EmailBundle\Helper\EmailDefaultsHelper;
use MailVotech\StageBundle\Entity\StageRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class EmailTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MockObject&FormBuilderInterface
     */
    private MockObject $formBuilder;

    private EmailType $form;

    /**
     * @var MockObject&ThemeHelperInterface
     */
    private MockObject $themeHelper;

    protected function setUp(): void
    {
        parent::setUp();

        $translator                 = $this->createMock(TranslatorInterface::class);
        $this->formBuilder          = $this->createMock(FormBuilderInterface::class);
        $corePermissions            = $this->createMock(CorePermissions::class);
        $this->themeHelper          = $this->createMock(ThemeHelperInterface::class);
        $emailConfig                = $this->createMock(EmailConfigInterface::class);
        $this->form                 = new EmailType(
            $translator,
            $this->createStub(EntityManager::class),
            $this->createStub(CoreParametersHelper::class),
            $this->themeHelper,
            $corePermissions,
            $emailConfig,
            $this->createStub(EmailDefaultsHelper::class),
            $this->createStub(StageRepository::class),
        );

        $this->formBuilder->method('create')->willReturnSelf();
        $this->formBuilder->method('add')->willReturnSelf();
        $this->formBuilder->method('addModelTransformer')->willReturnSelf();
        $corePermissions->method('hasPublishAccessForEntity')->willReturn(true);
        $translator->method('trans')->willReturn('translated');
        $emailConfig->method('isDraftEnabled')->willReturn(false);
    }

    public function testBuildForm(): void
    {
        $options = ['data' => new Email()];
        $names   = [];
        $this->expectThemeHelper();

        $this->formBuilder->method('add')
            ->with(
                $this->callback(
                    function (string|FormBuilderInterface $name) use (&$names): true {
                        $names[] = $name;

                        return true;
                    }
                )
            );

        $this->form->buildForm($this->formBuilder, $options);

        $this->assertContains('buttons', $names);
    }

    private function expectThemeHelper(): void
    {
        $this->themeHelper
            ->expects($this->once())
            ->method('getCurrentTheme')
            ->with('blank', 'email')
            ->willReturn('blank');
    }
}
