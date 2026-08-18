<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Tests\Unit\Form\Type;

use MailVotech\IntegrationsBundle\Exception\InvalidFormOptionException;
use MailVotech\IntegrationsBundle\Form\Type\IntegrationSyncSettingsObjectFieldType;
use MailVotech\IntegrationsBundle\Mapping\MappedFieldInfoInterface;
use MailVotech\IntegrationsBundle\Sync\DAO\Mapping\ObjectMappingDAO;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

final class IntegrationSyncSettingsObjectFieldTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MockObject&FormBuilderInterface
     */
    private MockObject $formBuilder;

    private IntegrationSyncSettingsObjectFieldType $form;

    protected function setUp(): void
    {
        parent::setUp();

        $this->formBuilder = $this->createMock(FormBuilderInterface::class);
        $this->form        = new IntegrationSyncSettingsObjectFieldType();
    }

    public function testBuildFormForWrongField(): void
    {
        $options = ['field' => 'unicorn'];
        $this->expectException(InvalidFormOptionException::class);
        $this->form->buildForm($this->formBuilder, $options);
    }

    public function testBuildFormForMappedField(): void
    {
        $field   = $this->createMock(MappedFieldInfoInterface::class);
        $options = [
            'field'        => $field,
            'placeholder'  => 'Placeholder ABC',
            'object'       => 'Object A',
            'integration'  => 'Integration A',
            'mailvotechFields' => [
                'mailvotech_field_a' => 'MailVotech Field A',
                'mailvotech_field_b' => 'MailVotech Field B',
            ],
        ];

        $field->method('showAsRequired')->willReturn(true);
        $field->method('getName')->willReturn('Integration Field A');
        $field->method('isBidirectionalSyncEnabled')->willReturn(false);
        $field->method('isToIntegrationSyncEnabled')->willReturn(true);
        $field->method('isToMailVotechSyncEnabled')->willReturn(true);
        $matcher = $this->exactly(2);

        $this->formBuilder->expects($matcher)
            ->method('add')->willReturnCallback(function (...$parameters) use ($matcher, $options): MockObject {
                if (1 === $matcher->numberOfInvocations()) {
                    $this->assertSame('mappedField', $parameters[0]);
                    $this->assertSame(ChoiceType::class, $parameters[1]);
                    $this->assertSame([
                        'label'          => false,
                        'choices'        => [
                            'MailVotech Field A' => 'mailvotech_field_a',
                            'MailVotech Field B' => 'mailvotech_field_b',
                        ],
                        'required'       => true,
                        'placeholder'    => '',
                        'error_bubbling' => false,
                        'attr'           => [
                            'class'            => 'form-control integration-mapped-field',
                            'data-placeholder' => $options['placeholder'],
                            'data-object'      => $options['object'],
                            'data-integration' => $options['integration'],
                            'data-field'       => 'Integration Field A',
                        ],
                    ], $parameters[2]);
                }
                if (2 === $matcher->numberOfInvocations()) {
                    $this->assertSame('syncDirection', $parameters[0]);
                    $this->assertSame(ChoiceType::class, $parameters[1]);
                    $this->assertSame([
                        'choices' => [
                            'mailvotech.integration.sync_direction_integration' => ObjectMappingDAO::SYNC_TO_INTEGRATION,
                            'mailvotech.integration.sync_direction_mailvotech'      => ObjectMappingDAO::SYNC_TO_MAILVOTECH,
                        ],
                        'label'      => false,
                        'empty_data' => ObjectMappingDAO::SYNC_TO_INTEGRATION,
                        'attr'       => [
                            'class'            => 'integration-sync-direction',
                            'data-object'      => 'Object A',
                            'data-integration' => 'Integration A',
                            'data-field'       => 'Integration Field A',
                        ],
                    ], $parameters[2]);
                }

                return $this->formBuilder;
            });

        $this->form->buildForm($this->formBuilder, $options);
    }
}
