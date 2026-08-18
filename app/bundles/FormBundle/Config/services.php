<?php

declare(strict_types=1);

use MailVotech\CoreBundle\DependencyInjection\MailVotechCoreExtension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return function (ContainerConfigurator $configurator): void {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->public();

    $excludes = [
        'ProgressiveProfiling/DisplayCounter.php',
        'ProgressiveProfiling/DisplayManager.php',
    ];

    $services->set(MailVotech\FormBundle\Event\Service\FieldValueTransformer::class);

    $services->load('MailVotech\\FormBundle\\', '../')
        ->exclude('../{'.implode(',', array_merge(MailVotechCoreExtension::DEFAULT_EXCLUDES, $excludes)).'}');

    $services->load('MailVotech\\FormBundle\\Entity\\', '../Entity/*Repository.php')
        ->tag(Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\ServiceRepositoryCompilerPass::REPOSITORY_SERVICE_TAG);
    $services->set('mailvotech.form.type.field', MailVotech\FormBundle\Form\Type\FieldType::class)
        ->call('setFieldModel', [service('mailvotech.form.model.field')])
        ->call('setFormModel', [service('mailvotech.form.model.form')]);
    $services->alias(MailVotech\FormBundle\Form\Type\FieldType::class, 'mailvotech.form.type.field');
    $services->set('mailvotech.form.type.form_submitaction_sendemail', MailVotech\FormBundle\Form\Type\SubmitActionEmailType::class)
        ->call('setFieldModel', [service('mailvotech.form.model.field')])
        ->call('setFormModel', [service('mailvotech.form.model.form')]);
    $services->alias(MailVotech\FormBundle\Form\Type\SubmitActionEmailType::class, 'mailvotech.form.type.form_submitaction_sendemail');
    $services->set('mailvotech.form.type.form_submitaction_repost', MailVotech\FormBundle\Form\Type\SubmitActionRepostType::class)
        ->call('setFieldModel', [service('mailvotech.form.model.field')])
        ->call('setFormModel', [service('mailvotech.form.model.form')]);
    $services->alias(MailVotech\FormBundle\Form\Type\SubmitActionRepostType::class, 'mailvotech.form.type.form_submitaction_repost');
    $services->set('mailvotech.form.fixture.form', MailVotech\FormBundle\DataFixtures\ORM\LoadFormData::class)->tag(Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG);
    $services->alias(MailVotech\FormBundle\DataFixtures\ORM\LoadFormData::class, 'mailvotech.form.fixture.form');
    $services->set('mailvotech.form.fixture.form_result', MailVotech\FormBundle\DataFixtures\ORM\LoadFormResultData::class)->tag(Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG);
    $services->alias(MailVotech\FormBundle\DataFixtures\ORM\LoadFormResultData::class, 'mailvotech.form.fixture.form_result');
    $services->set('mailvotech.form.collector.object', MailVotech\FormBundle\Collector\ObjectCollector::class);
    $services->alias(MailVotech\FormBundle\Collector\ObjectCollector::class, 'mailvotech.form.collector.object');
    $services->set('mailvotech.form.collector.field', MailVotech\FormBundle\Collector\FieldCollector::class);
    $services->alias(MailVotech\FormBundle\Collector\FieldCollector::class, 'mailvotech.form.collector.field');
    $services->set('mailvotech.form.collector.mapped.object', MailVotech\FormBundle\Collector\MappedObjectCollector::class);
    $services->alias(MailVotech\FormBundle\Collector\MappedObjectCollector::class, 'mailvotech.form.collector.mapped.object');
    $services->set('mailvotech.form.collector.already.mapped.field', MailVotech\FormBundle\Collector\AlreadyMappedFieldCollector::class);
    $services->alias(MailVotech\FormBundle\Collector\AlreadyMappedFieldCollector::class, 'mailvotech.form.collector.already.mapped.field');
    $services->set('mailvotech.helper.form.field_helper', MailVotech\FormBundle\Helper\FormFieldHelper::class);
    $services->alias(MailVotech\FormBundle\Helper\FormFieldHelper::class, 'mailvotech.helper.form.field_helper');
    $services->set('mailvotech.form.helper.form_uploader', MailVotech\FormBundle\Helper\FormUploader::class);
    $services->alias(MailVotech\FormBundle\Helper\FormUploader::class, 'mailvotech.form.helper.form_uploader');
    $services->set('mailvotech.form.helper.token', MailVotech\FormBundle\Helper\TokenHelper::class);
    $services->alias(MailVotech\FormBundle\Helper\TokenHelper::class, 'mailvotech.form.helper.token');

    $services->set('mailvotech.form.helper.properties.accessor', MailVotech\FormBundle\Helper\PropertiesAccessor::class);
    $services->alias(MailVotech\FormBundle\Helper\PropertiesAccessor::class, 'mailvotech.form.helper.properties.accessor');
    $services->set('mailvotech.form.validator.upload_field_validator', MailVotech\FormBundle\Validator\UploadFieldValidator::class);
    $services->alias(MailVotech\FormBundle\Validator\UploadFieldValidator::class, 'mailvotech.form.validator.upload_field_validator');

    $services->set(MailVotech\FormBundle\Validator\Constraint\FileExtensionConstraintValidator::class)
        ->tag('validator.constraint_validator', ['alias' => 'file_extension_constraint_validator']);

    $services->set('mailvotech.form.command.form_submissions_records_clean', MailVotech\FormBundle\Command\DeleteOrphanSubmissionRecordsFromFormResultsTableCommand::class)->tag('console.command');
    $services->alias(MailVotech\FormBundle\Command\DeleteOrphanSubmissionRecordsFromFormResultsTableCommand::class, 'mailvotech.form.command.form_submissions_records_clean');
    $services->set('mailvotech.form.command.form_submissions_table_clean', MailVotech\FormBundle\Command\DeleteOrphanFormResultsTableCommand::class)->tag('console.command');
    $services->alias(MailVotech\FormBundle\Command\DeleteOrphanFormResultsTableCommand::class, 'mailvotech.form.command.form_submissions_table_clean');

    $services->alias('mailvotech.form.model.action', MailVotech\FormBundle\Model\ActionModel::class);
    $services->alias('mailvotech.form.model.field', MailVotech\FormBundle\Model\FieldModel::class);
    $services->alias('mailvotech.form.model.form', MailVotech\FormBundle\Model\FormModel::class);
    $services->alias('mailvotech.form.model.submission', MailVotech\FormBundle\Model\SubmissionModel::class);
    $services->alias('mailvotech.form.model.submission_result_loader', MailVotech\FormBundle\Model\SubmissionResultLoader::class);
    $services->alias('mailvotech.form.repository.form', MailVotech\FormBundle\Entity\FormRepository::class);
    $services->alias('mailvotech.form.repository.submission', MailVotech\FormBundle\Entity\SubmissionRepository::class);
};
