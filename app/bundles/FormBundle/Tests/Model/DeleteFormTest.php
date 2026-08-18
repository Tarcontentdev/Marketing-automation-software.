<?php

declare(strict_types=1);

namespace MailVotech\FormBundle\Tests\Model;

use Doctrine\ORM\EntityManagerInterface;
use MailVotech\CoreBundle\Doctrine\Helper\ColumnSchemaHelper;
use MailVotech\CoreBundle\Doctrine\Helper\TableSchemaHelper;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Helper\ThemeHelperInterface;
use MailVotech\CoreBundle\Helper\UserHelper;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\CoreBundle\Translation\Translator;
use MailVotech\FormBundle\Collector\MappedObjectCollectorInterface;
use MailVotech\FormBundle\Entity\Form;
use MailVotech\FormBundle\Entity\FormRepository;
use MailVotech\FormBundle\Helper\FormFieldHelper;
use MailVotech\FormBundle\Helper\FormUploader;
use MailVotech\FormBundle\Model\ActionModel;
use MailVotech\FormBundle\Model\FieldModel;
use MailVotech\FormBundle\Model\FormModel;
use MailVotech\LeadBundle\Helper\PrimaryCompanyHelper;
use MailVotech\LeadBundle\Model\FieldModel as LeadFieldModel;
use MailVotech\LeadBundle\Tracker\ContactTracker;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

final class DeleteFormTest extends \PHPUnit\Framework\TestCase
{
    public function testDelete(): void
    {
        $requestStack          = $this->createStub(RequestStack::class);
        $twigMock              = $this->createStub(Environment::class);
        $themeHelper           = $this->createStub(ThemeHelperInterface::class);
        $formActionModel       = $this->createStub(ActionModel::class);
        $formFieldModel        = $this->createStub(FieldModel::class);
        $fieldHelper           = $this->createStub(FormFieldHelper::class);
        $primaryCompanyHelper  = $this->createStub(PrimaryCompanyHelper::class);
        $leadFieldModel        = $this->createStub(LeadFieldModel::class);
        $formUploaderMock      = $this->createMock(FormUploader::class);
        $contactTracker        = $this->createStub(ContactTracker::class);
        $columnSchemaHelper    = $this->createStub(ColumnSchemaHelper::class);
        $tableSchemaHelper     = $this->createStub(TableSchemaHelper::class);
        $entityManager         = $this->createStub(EntityManagerInterface::class);
        $dispatcher            = $this->createMock(EventDispatcher::class);
        $formRepository        = $this->createMock(FormRepository::class);
        $form                  = $this->createMock(Form::class);
        $mappedObjectCollector = $this->createStub(MappedObjectCollectorInterface::class);
        $formModel             = new FormModel(
            $requestStack,
            $twigMock,
            $themeHelper,
            $formActionModel,
            $formFieldModel,
            $fieldHelper,
            $primaryCompanyHelper,
            $leadFieldModel,
            $formUploaderMock,
            $contactTracker,
            $columnSchemaHelper,
            $tableSchemaHelper,
            $mappedObjectCollector,
            $entityManager,
            $this->createStub(CorePermissions::class),
            $dispatcher,
            $this->createStub(UrlGeneratorInterface::class),
            $this->createStub(Translator::class),
            $this->createStub(UserHelper::class),
            $this->createStub(LoggerInterface::class),
            $this->createStub(CoreParametersHelper::class),
            $formRepository
        );
        $matcher = $this->exactly(2);

        $dispatcher->expects($matcher)
            ->method('hasListeners')->willReturnCallback(function (...$parameters) use ($matcher): false {
                if (1 === $matcher->numberOfInvocations()) {
                    $this->assertSame('mailvotech.form_pre_delete', $parameters[0]);
                }
                if (2 === $matcher->numberOfInvocations()) {
                    $this->assertSame('mailvotech.form_post_delete', $parameters[0]);
                }

                return false;
            });

        $form->expects($this->exactly(2))
            ->method('getId')
            ->with()
            ->willReturn(1);

        $formUploaderMock->expects($this->once())
            ->method('deleteFilesOfForm')
            ->with($form);

        $formRepository->expects($this->once())
            ->method('deleteEntity')
            ->with($form);

        $formModel->deleteEntity($form);

        $this->assertSame(1, $form->deletedId);
    }
}
