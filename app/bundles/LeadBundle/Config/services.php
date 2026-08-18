<?php

declare(strict_types=1);

use MailVotech\CoreBundle\DependencyInjection\MailVotechCoreExtension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $configurator): void {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->public();

    $excludes = [
        'Deduplicate/Exception',
        'Field/DTO',
        'Field/Event',
        'Segment/ContactSegmentFilter.php',
        'Segment/ContactSegmentFilterCrate.php',
        'Segment/Decorator',
        'Segment/DoNotContact',
        'Segment/IntegrationCampaign',
        'Segment/Query',
        'Segment/Stat',
        'Form/Validator/Constraints/UniqueCustomField.php',
        'Validator/Constraints/SegmentDate.php',
    ];

    $services->load('MailVotech\\LeadBundle\\', '../')
        ->exclude('../{'.implode(',', array_merge(MailVotechCoreExtension::DEFAULT_EXCLUDES, $excludes)).'}');

    $services->load('MailVotech\\LeadBundle\\Entity\\', '../Entity/*Repository.php')
        ->tag(Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\ServiceRepositoryCompilerPass::REPOSITORY_SERVICE_TAG);
    $services->set('mailvotech.lead.serializer.subscriber', MailVotech\LeadBundle\EventListener\SerializerSubscriber::class)->tag('jms_serializer.event_subscriber', ['event' => JMS\Serializer\EventDispatcher\Events::POST_SERIALIZE]);
    $services->alias(MailVotech\LeadBundle\EventListener\SerializerSubscriber::class, 'mailvotech.lead.serializer.subscriber');
    $services->set(MailVotech\LeadBundle\Form\Validator\Constraints\FieldAliasKeywordValidator::class)->tag('validator.constraint_validator');
    $services->set(MailVotech\CoreBundle\Form\Validator\Constraints\FileEncodingValidator::class)->tag('validator.constraint_validator');
    $services->set('mailvotech.validator.leadlistaccess', MailVotech\LeadBundle\Form\Validator\Constraints\LeadListAccessValidator::class)->tag('validator.constraint_validator', ['alias' => 'leadlist_access']);
    $services->alias(MailVotech\LeadBundle\Form\Validator\Constraints\LeadListAccessValidator::class, 'mailvotech.validator.leadlistaccess');
    $services->set('mailvotech.lead.constraint.alias', MailVotech\LeadBundle\Form\Validator\Constraints\UniqueUserAliasValidator::class)->tag('validator.constraint_validator', ['alias' => 'uniqueleadlist']);
    $services->alias(MailVotech\LeadBundle\Form\Validator\Constraints\UniqueUserAliasValidator::class, 'mailvotech.lead.constraint.alias');
    $services->set('mailvotech.lead_list.constraint.in_use', MailVotech\LeadBundle\Form\Validator\Constraints\SegmentInUseValidator::class)->tag('validator.constraint_validator', ['alias' => 'segment_in_use']);
    $services->alias(MailVotech\LeadBundle\Form\Validator\Constraints\SegmentInUseValidator::class, 'mailvotech.lead_list.constraint.in_use');
    $services->set('mailvotech.helper.twig.avatar', MailVotech\LeadBundle\Twig\Helper\AvatarHelper::class)->tag('twig.helper', ['alias' => 'lead_avatar']);
    $services->alias(MailVotech\LeadBundle\Twig\Helper\AvatarHelper::class, 'mailvotech.helper.twig.avatar');
    $services->set('mailvotech.helper.twig.default_avatar', MailVotech\LeadBundle\Twig\Helper\DefaultAvatarHelper::class)->tag('twig.helper', ['alias' => 'default_avatar']);
    $services->alias(MailVotech\LeadBundle\Twig\Helper\DefaultAvatarHelper::class, 'mailvotech.helper.twig.default_avatar');
    $services->set('mailvotech.helper.twig.dnc_reason', MailVotech\LeadBundle\Twig\Helper\DncReasonHelper::class)->tag('twig.helper', ['alias' => 'lead_dnc_reason']);
    $services->alias(MailVotech\LeadBundle\Twig\Helper\DncReasonHelper::class, 'mailvotech.helper.twig.dnc_reason');
    $services->set('mailvotech.lead.fixture.test.click', MailVotech\LeadBundle\Tests\DataFixtures\ORM\LoadClickData::class)->tag(Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG);
    $services->alias(MailVotech\LeadBundle\Tests\DataFixtures\ORM\LoadClickData::class, 'mailvotech.lead.fixture.test.click');
    $services->set('mailvotech.lead.fixture.test.dnc', MailVotech\LeadBundle\Tests\DataFixtures\ORM\LoadDncData::class)->tag(Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG);
    $services->alias(MailVotech\LeadBundle\Tests\DataFixtures\ORM\LoadDncData::class, 'mailvotech.lead.fixture.test.dnc');
    $services->set('mailvotech.lead.fixture.test.tag', MailVotech\LeadBundle\Tests\DataFixtures\ORM\LoadTagData::class)->tag(Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG);
    $services->alias(MailVotech\LeadBundle\Tests\DataFixtures\ORM\LoadTagData::class, 'mailvotech.lead.fixture.test.tag');
    $services->set('mailvotech.lead.fixture.test.page_hit', MailVotech\LeadBundle\Tests\DataFixtures\ORM\LoadPageHitData::class)->tag(Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG);
    $services->alias(MailVotech\LeadBundle\Tests\DataFixtures\ORM\LoadPageHitData::class, 'mailvotech.lead.fixture.test.page_hit');
    $services->set('mailvotech.lead.fixture.test.segment', MailVotech\LeadBundle\Tests\DataFixtures\ORM\LoadSegmentsData::class)->tag(Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG);
    $services->alias(MailVotech\LeadBundle\Tests\DataFixtures\ORM\LoadSegmentsData::class, 'mailvotech.lead.fixture.test.segment');
    $services->set('mailvotech.lead.fixture.company', MailVotech\LeadBundle\DataFixtures\ORM\LoadCompanyData::class)->tag(Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG);
    $services->alias(MailVotech\LeadBundle\DataFixtures\ORM\LoadCompanyData::class, 'mailvotech.lead.fixture.company');
    $services->set('mailvotech.lead.fixture.contact', MailVotech\LeadBundle\DataFixtures\ORM\LoadLeadData::class)->tag(Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG);
    $services->alias(MailVotech\LeadBundle\DataFixtures\ORM\LoadLeadData::class, 'mailvotech.lead.fixture.contact');
    $services->set('mailvotech.lead.fixture.segment', MailVotech\LeadBundle\DataFixtures\ORM\LoadLeadListData::class)->tag(Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG);
    $services->alias(MailVotech\LeadBundle\DataFixtures\ORM\LoadLeadListData::class, 'mailvotech.lead.fixture.segment');
    $services->set('mailvotech.lead.fixture.category', MailVotech\LeadBundle\DataFixtures\ORM\LoadCategoryData::class)->tag(Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG);
    $services->alias(MailVotech\LeadBundle\DataFixtures\ORM\LoadCategoryData::class, 'mailvotech.lead.fixture.category');
    $services->set('mailvotech.lead.fixture.categorizedleadlists', MailVotech\LeadBundle\DataFixtures\ORM\LoadCategorizedLeadListData::class)->tag(Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG);
    $services->alias(MailVotech\LeadBundle\DataFixtures\ORM\LoadCategorizedLeadListData::class, 'mailvotech.lead.fixture.categorizedleadlists');
    $services->set('mailvotech.lead.export_scheduled_audit_log_subscriber', MailVotech\LeadBundle\EventListener\ContactExportSchedulerAuditLogSubscriber::class);
    $services->alias(MailVotech\LeadBundle\EventListener\ContactExportSchedulerAuditLogSubscriber::class, 'mailvotech.lead.export_scheduled_audit_log_subscriber');
    $services->set('mailvotech.lead.export_scheduled_logger_subscriber', MailVotech\LeadBundle\EventListener\ContactExportSchedulerLoggerSubscriber::class);
    $services->alias(MailVotech\LeadBundle\EventListener\ContactExportSchedulerLoggerSubscriber::class, 'mailvotech.lead.export_scheduled_logger_subscriber');
    $services->set('mailvotech.lead.contact_scheduled_export.subscriber', MailVotech\LeadBundle\EventListener\ContactScheduledExportSubscriber::class);
    $services->alias(MailVotech\LeadBundle\EventListener\ContactScheduledExportSubscriber::class, 'mailvotech.lead.contact_scheduled_export.subscriber');
    $services->set('mailvotech.validator.emailaddress', MailVotech\LeadBundle\Form\Validator\Constraints\EmailAddressValidator::class)->tag('validator.constraint_validator');
    $services->alias(MailVotech\LeadBundle\Form\Validator\Constraints\EmailAddressValidator::class, 'mailvotech.validator.emailaddress');
    $services->set('mailvotech.lead.validator.custom_field', MailVotech\LeadBundle\Validator\CustomFieldValidator::class);
    $services->alias(MailVotech\LeadBundle\Validator\CustomFieldValidator::class, 'mailvotech.lead.validator.custom_field');
    $services->set('mailvotech.lead.validator.lead.list.campaign', MailVotech\LeadBundle\Validator\SegmentUsedInCampaignsValidator::class);
    $services->alias(MailVotech\LeadBundle\Validator\SegmentUsedInCampaignsValidator::class, 'mailvotech.lead.validator.lead.list.campaign');
    $services->set('mailvotech.lead.constraint.validator.lead.list.campaign', MailVotech\LeadBundle\Validator\Constraints\SegmentUsedInCampaignsValidator::class)->tag('validator.constraint_validator');
    $services->alias(MailVotech\LeadBundle\Validator\Constraints\SegmentUsedInCampaignsValidator::class, 'mailvotech.lead.constraint.validator.lead.list.campaign');
    $services->set('mailvotech.lead.event.dispatcher', MailVotech\LeadBundle\Helper\LeadChangeEventDispatcher::class);
    $services->alias(MailVotech\LeadBundle\Helper\LeadChangeEventDispatcher::class, 'mailvotech.lead.event.dispatcher');
    $services->set('mailvotech.lead.merger', MailVotech\LeadBundle\Deduplicate\ContactMerger::class);
    $services->alias(MailVotech\LeadBundle\Deduplicate\ContactMerger::class, 'mailvotech.lead.merger');
    $services->set('mailvotech.lead.deduper', MailVotech\LeadBundle\Deduplicate\ContactDeduper::class);
    $services->alias(MailVotech\LeadBundle\Deduplicate\ContactDeduper::class, 'mailvotech.lead.deduper');
    $services->set('mailvotech.lead.helper.primary_company', MailVotech\LeadBundle\Helper\PrimaryCompanyHelper::class);
    $services->alias(MailVotech\LeadBundle\Helper\PrimaryCompanyHelper::class, 'mailvotech.lead.helper.primary_company');
    $services->set('mailvotech.lead.validator.length', MailVotech\LeadBundle\Validator\Constraints\LengthValidator::class)->tag('validator.constraint_validator');
    $services->alias(MailVotech\LeadBundle\Validator\Constraints\LengthValidator::class, 'mailvotech.lead.validator.length');
    $services->set('mailvotech.lead.segment.stat.dependencies', MailVotech\LeadBundle\Segment\Stat\SegmentDependencies::class);
    $services->alias(MailVotech\LeadBundle\Segment\Stat\SegmentDependencies::class, 'mailvotech.lead.segment.stat.dependencies');

    $services->set(MailVotech\LeadBundle\Segment\Stat\SegmentChartQueryFactory::class);

    $services->set('mailvotech.lead.segment.stat.campaign.share', MailVotech\LeadBundle\Segment\Stat\SegmentCampaignShare::class);
    $services->alias(MailVotech\LeadBundle\Segment\Stat\SegmentCampaignShare::class, 'mailvotech.lead.segment.stat.campaign.share');
    $services->set('mailvotech.lead.columns.dictionary', MailVotech\LeadBundle\Services\ContactColumnsDictionary::class);
    $services->alias(MailVotech\LeadBundle\Services\ContactColumnsDictionary::class, 'mailvotech.lead.columns.dictionary');
    $services->set('mailvotech.lead.model.lead_segment_filter_factory', MailVotech\LeadBundle\Segment\ContactSegmentFilterFactory::class);
    $services->alias(MailVotech\LeadBundle\Segment\ContactSegmentFilterFactory::class, 'mailvotech.lead.model.lead_segment_filter_factory');
    $services->set('mailvotech.tracker.device', MailVotech\LeadBundle\Tracker\DeviceTracker::class);
    $services->alias(MailVotech\LeadBundle\Tracker\DeviceTracker::class, 'mailvotech.tracker.device');
    $services->set('mailvotech.lead.field.custom_field_column', MailVotech\LeadBundle\Field\CustomFieldColumn::class);
    $services->alias(MailVotech\LeadBundle\Field\CustomFieldColumn::class, 'mailvotech.lead.field.custom_field_column');
    $services->set('mailvotech.lead.field.custom_field_index', MailVotech\LeadBundle\Field\CustomFieldIndex::class);
    $services->alias(MailVotech\LeadBundle\Field\CustomFieldIndex::class, 'mailvotech.lead.field.custom_field_index');
    $services->set('mailvotech.lead.repository.lead_segment_filter_descriptor', MailVotech\LeadBundle\Services\ContactSegmentFilterDictionary::class);
    $services->alias(MailVotech\LeadBundle\Services\ContactSegmentFilterDictionary::class, 'mailvotech.lead.repository.lead_segment_filter_descriptor');
    $services->set('mailvotech.lead.service.segment_dependency_tree_factory', MailVotech\LeadBundle\Services\SegmentDependencyTreeFactory::class);
    $services->alias(MailVotech\LeadBundle\Services\SegmentDependencyTreeFactory::class, 'mailvotech.lead.service.segment_dependency_tree_factory');
    $services->set('mailvotech.lead.repository.lead_segment_query_builder', MailVotech\LeadBundle\Segment\Query\ContactSegmentQueryBuilder::class);
    $services->alias(MailVotech\LeadBundle\Segment\Query\ContactSegmentQueryBuilder::class, 'mailvotech.lead.repository.lead_segment_query_builder');
    $services->set('mailvotech.lead.model.lead_segment_service', MailVotech\LeadBundle\Segment\ContactSegmentService::class);
    $services->alias(MailVotech\LeadBundle\Segment\ContactSegmentService::class, 'mailvotech.lead.model.lead_segment_service');
    $services->set('mailvotech.lead.model.lead_segment_schema_cache', MailVotech\LeadBundle\Segment\TableSchemaColumnsCache::class);
    $services->alias(MailVotech\LeadBundle\Segment\TableSchemaColumnsCache::class, 'mailvotech.lead.model.lead_segment_schema_cache');
    $services->set('mailvotech.lead.model.relative_date', MailVotech\LeadBundle\Segment\RelativeDate::class);
    $services->alias(MailVotech\LeadBundle\Segment\RelativeDate::class, 'mailvotech.lead.model.relative_date');
    $services->set('mailvotech.lead.model.lead_segment_filter_operator', MailVotech\LeadBundle\Segment\ContactSegmentFilterOperator::class);
    $services->alias(MailVotech\LeadBundle\Segment\ContactSegmentFilterOperator::class, 'mailvotech.lead.model.lead_segment_filter_operator');
    $services->set('mailvotech.lead.model.lead_segment_decorator_factory', MailVotech\LeadBundle\Segment\Decorator\DecoratorFactory::class);
    $services->alias(MailVotech\LeadBundle\Segment\Decorator\DecoratorFactory::class, 'mailvotech.lead.model.lead_segment_decorator_factory');
    $services->set('mailvotech.lead.model.lead_segment_decorator_base', MailVotech\LeadBundle\Segment\Decorator\BaseDecorator::class);
    $services->alias(MailVotech\LeadBundle\Segment\Decorator\BaseDecorator::class, 'mailvotech.lead.model.lead_segment_decorator_base');
    $services->set('mailvotech.lead.model.lead_segment_decorator_custom_mapped', MailVotech\LeadBundle\Segment\Decorator\CustomMappedDecorator::class);
    $services->alias(MailVotech\LeadBundle\Segment\Decorator\CustomMappedDecorator::class, 'mailvotech.lead.model.lead_segment_decorator_custom_mapped');
    $services->set('mailvotech.lead.model.lead_segment_decorator_company', MailVotech\LeadBundle\Segment\Decorator\CompanyDecorator::class);
    $services->alias(MailVotech\LeadBundle\Segment\Decorator\CompanyDecorator::class, 'mailvotech.lead.model.lead_segment_decorator_company');
    $services->set('mailvotech.lead.model.lead_segment_decorator_date', MailVotech\LeadBundle\Segment\Decorator\DateDecorator::class);
    $services->alias(MailVotech\LeadBundle\Segment\Decorator\DateDecorator::class, 'mailvotech.lead.model.lead_segment_decorator_date');
    $services->set('mailvotech.lead.model.lead_segment.decorator.date.optionFactory', MailVotech\LeadBundle\Segment\Decorator\Date\DateOptionFactory::class);
    $services->alias(MailVotech\LeadBundle\Segment\Decorator\Date\DateOptionFactory::class, 'mailvotech.lead.model.lead_segment.decorator.date.optionFactory');
    $services->set('mailvotech.lead.model.lead_segment.timezoneResolver', MailVotech\LeadBundle\Segment\Decorator\Date\TimezoneResolver::class);
    $services->alias(MailVotech\LeadBundle\Segment\Decorator\Date\TimezoneResolver::class, 'mailvotech.lead.model.lead_segment.timezoneResolver');
    $services->set('mailvotech.lead.provider.fillterOperator', MailVotech\LeadBundle\Provider\FilterOperatorProvider::class);
    $services->alias(MailVotech\LeadBundle\Provider\FilterOperatorProvider::class, 'mailvotech.lead.provider.fillterOperator');
    $services->set('mailvotech.lead.provider.typeOperator', MailVotech\LeadBundle\Provider\TypeOperatorProvider::class);
    $services->alias(MailVotech\LeadBundle\Provider\TypeOperatorProvider::class, 'mailvotech.lead.provider.typeOperator');
    $services->set('mailvotech.lead.provider.fieldChoices', MailVotech\LeadBundle\Provider\FieldChoicesProvider::class);
    $services->alias(MailVotech\LeadBundle\Provider\FieldChoicesProvider::class, 'mailvotech.lead.provider.fieldChoices');
    $services->set('mailvotech.lead.provider.formAdjustments', MailVotech\LeadBundle\Provider\FormAdjustmentsProvider::class);
    $services->alias(MailVotech\LeadBundle\Provider\FormAdjustmentsProvider::class, 'mailvotech.lead.provider.formAdjustments');
    $services->set('mailvotech.lead.model.random_parameter_name', MailVotech\LeadBundle\Segment\RandomParameterName::class);
    $services->alias(MailVotech\LeadBundle\Segment\RandomParameterName::class, 'mailvotech.lead.model.random_parameter_name');
    $services->set('mailvotech.lead.segment.operator_options', MailVotech\LeadBundle\Segment\OperatorOptions::class);
    $services->alias(MailVotech\LeadBundle\Segment\OperatorOptions::class, 'mailvotech.lead.segment.operator_options');
    $services->set('mailvotech.lead.reportbundle.fields_builder', MailVotech\LeadBundle\Report\FieldsBuilder::class);
    $services->alias(MailVotech\LeadBundle\Report\FieldsBuilder::class, 'mailvotech.lead.reportbundle.fields_builder');
    $services->set('mailvotech.lead.factory.device_detector_factory', MailVotech\LeadBundle\Tracker\Factory\DeviceDetectorFactory\DeviceDetectorFactory::class);
    $services->alias(MailVotech\LeadBundle\Tracker\Factory\DeviceDetectorFactory\DeviceDetectorFactory::class, 'mailvotech.lead.factory.device_detector_factory');
    $services->set('mailvotech.lead.service.contact_tracking_service', MailVotech\LeadBundle\Tracker\Service\ContactTrackingService\ContactTrackingService::class);
    $services->alias(MailVotech\LeadBundle\Tracker\Service\ContactTrackingService\ContactTrackingService::class, 'mailvotech.lead.service.contact_tracking_service');
    $services->set('mailvotech.lead.service.device_creator_service', MailVotech\LeadBundle\Tracker\Service\DeviceCreatorService\DeviceCreatorService::class);
    $services->alias(MailVotech\LeadBundle\Tracker\Service\DeviceCreatorService\DeviceCreatorService::class, 'mailvotech.lead.service.device_creator_service');
    $services->set('mailvotech.lead.service.device_tracking_service', MailVotech\LeadBundle\Tracker\Service\DeviceTrackingService\DeviceTrackingService::class);
    $services->alias(MailVotech\LeadBundle\Tracker\Service\DeviceTrackingService\DeviceTrackingService::class, 'mailvotech.lead.service.device_tracking_service');
    $services->set('mailvotech.lead.field.schema_definition', MailVotech\LeadBundle\Field\SchemaDefinition::class);
    $services->alias(MailVotech\LeadBundle\Field\SchemaDefinition::class, 'mailvotech.lead.field.schema_definition');
    $services->set('mailvotech.lead.field.dispatcher.field_save_dispatcher', MailVotech\LeadBundle\Field\Dispatcher\FieldSaveDispatcher::class);
    $services->alias(MailVotech\LeadBundle\Field\Dispatcher\FieldSaveDispatcher::class, 'mailvotech.lead.field.dispatcher.field_save_dispatcher');
    $services->set('mailvotech.lead.field.dispatcher.field_column_dispatcher', MailVotech\LeadBundle\Field\Dispatcher\FieldColumnDispatcher::class);
    $services->alias(MailVotech\LeadBundle\Field\Dispatcher\FieldColumnDispatcher::class, 'mailvotech.lead.field.dispatcher.field_column_dispatcher');
    $services->set('mailvotech.lead.field.dispatcher.field_column_background_dispatcher', MailVotech\LeadBundle\Field\Dispatcher\FieldColumnBackgroundJobDispatcher::class);
    $services->alias(MailVotech\LeadBundle\Field\Dispatcher\FieldColumnBackgroundJobDispatcher::class, 'mailvotech.lead.field.dispatcher.field_column_background_dispatcher');
    $services->set('mailvotech.lead.field.fields_with_unique_identifier', MailVotech\LeadBundle\Field\FieldsWithUniqueIdentifier::class);
    $services->alias(MailVotech\LeadBundle\Field\FieldsWithUniqueIdentifier::class, 'mailvotech.lead.field.fields_with_unique_identifier');
    $services->set('mailvotech.lead.field.field_list', MailVotech\LeadBundle\Field\FieldList::class);
    $services->alias(MailVotech\LeadBundle\Field\FieldList::class, 'mailvotech.lead.field.field_list');
    $services->set('mailvotech.lead.field.identifier_fields', MailVotech\LeadBundle\Field\IdentifierFields::class);
    $services->alias(MailVotech\LeadBundle\Field\IdentifierFields::class, 'mailvotech.lead.field.identifier_fields');
    $services->set('mailvotech.lead.field.lead_field_saver', MailVotech\LeadBundle\Field\LeadFieldSaver::class);
    $services->alias(MailVotech\LeadBundle\Field\LeadFieldSaver::class, 'mailvotech.lead.field.lead_field_saver');
    $services->set('mailvotech.lead.field.settings.background_settings', MailVotech\LeadBundle\Field\Settings\BackgroundSettings::class);
    $services->alias(MailVotech\LeadBundle\Field\Settings\BackgroundSettings::class, 'mailvotech.lead.field.settings.background_settings');
    $services->set('mailvotech.lead.field.notification.custom_field', MailVotech\LeadBundle\Field\Notification\CustomFieldNotification::class);
    $services->alias(MailVotech\LeadBundle\Field\Notification\CustomFieldNotification::class, 'mailvotech.lead.field.notification.custom_field');
    $services->set('mailvotech.lead.query.builder.basic', MailVotech\LeadBundle\Segment\Query\Filter\BaseFilterQueryBuilder::class);
    $services->alias(MailVotech\LeadBundle\Segment\Query\Filter\BaseFilterQueryBuilder::class, 'mailvotech.lead.query.builder.basic');
    $services->set('mailvotech.lead.query.builder.foreign.value', MailVotech\LeadBundle\Segment\Query\Filter\ForeignValueFilterQueryBuilder::class);
    $services->alias(MailVotech\LeadBundle\Segment\Query\Filter\ForeignValueFilterQueryBuilder::class, 'mailvotech.lead.query.builder.foreign.value');
    $services->set('mailvotech.lead.query.builder.foreign.func', MailVotech\LeadBundle\Segment\Query\Filter\ForeignFuncFilterQueryBuilder::class);
    $services->alias(MailVotech\LeadBundle\Segment\Query\Filter\ForeignFuncFilterQueryBuilder::class, 'mailvotech.lead.query.builder.foreign.func');
    $services->set('mailvotech.lead.query.builder.special.dnc', MailVotech\LeadBundle\Segment\Query\Filter\DoNotContactFilterQueryBuilder::class);
    $services->alias(MailVotech\LeadBundle\Segment\Query\Filter\DoNotContactFilterQueryBuilder::class, 'mailvotech.lead.query.builder.special.dnc');
    $services->set('mailvotech.lead.query.builder.special.integration', MailVotech\LeadBundle\Segment\Query\Filter\IntegrationCampaignFilterQueryBuilder::class);
    $services->alias(MailVotech\LeadBundle\Segment\Query\Filter\IntegrationCampaignFilterQueryBuilder::class, 'mailvotech.lead.query.builder.special.integration');
    $services->set('mailvotech.lead.query.builder.special.sessions', MailVotech\LeadBundle\Segment\Query\Filter\SessionsFilterQueryBuilder::class);
    $services->alias(MailVotech\LeadBundle\Segment\Query\Filter\SessionsFilterQueryBuilder::class, 'mailvotech.lead.query.builder.special.sessions');
    $services->set('mailvotech.lead.query.builder.complex_relation.value', MailVotech\LeadBundle\Segment\Query\Filter\ComplexRelationValueFilterQueryBuilder::class);
    $services->alias(MailVotech\LeadBundle\Segment\Query\Filter\ComplexRelationValueFilterQueryBuilder::class, 'mailvotech.lead.query.builder.complex_relation.value');
    $services->set('mailvotech.lead.query.builder.channel_click.value', MailVotech\LeadBundle\Segment\Query\Filter\ChannelClickQueryBuilder::class);
    $services->alias(MailVotech\LeadBundle\Segment\Query\Filter\ChannelClickQueryBuilder::class, 'mailvotech.lead.query.builder.channel_click.value');
    $services->set('mailvotech.helper.field.alias', MailVotech\LeadBundle\Helper\FieldAliasHelper::class);
    $services->alias(MailVotech\LeadBundle\Helper\FieldAliasHelper::class, 'mailvotech.helper.field.alias');
    $services->alias('mailvotech.lead.model.lead', MailVotech\LeadBundle\Model\LeadModel::class);
    $services->get(MailVotech\LeadBundle\Entity\CompanyRepository::class)
        ->call('setUniqueIdentifiersOperator', ['%mailvotech.company_unique_identifiers_operator%']);
    $services->get(MailVotech\LeadBundle\Entity\LeadRepository::class)
        ->call('setUniqueIdentifiersOperator', ['%mailvotech.contact_unique_identifiers_operator%'])
        ->call('setListLeadRepository', [\Symfony\Component\DependencyInjection\Loader\Configurator\service('mailvotech.lead.repository.list_lead')]);

    $services->alias('mailvotech.lead.model.field', MailVotech\LeadBundle\Model\FieldModel::class);
    $services->alias('mailvotech.lead.model.list', MailVotech\LeadBundle\Model\ListModel::class);
    $services->alias('mailvotech.lead.model.note', MailVotech\LeadBundle\Model\NoteModel::class);
    $services->alias('mailvotech.lead.model.device', MailVotech\LeadBundle\Model\DeviceModel::class);
    $services->alias('mailvotech.lead.model.company', MailVotech\LeadBundle\Model\CompanyModel::class);
    $services->alias('mailvotech.lead.model.import', MailVotech\LeadBundle\Model\ImportModel::class);
    $services->alias('mailvotech.lead.model.tag', MailVotech\LeadBundle\Model\TagModel::class);
    $services->alias('mailvotech.lead.model.company_report_data', MailVotech\LeadBundle\Model\CompanyReportData::class);
    $services->alias('mailvotech.lead.model.dnc', MailVotech\LeadBundle\Model\DoNotContact::class);
    $services->alias('mailvotech.lead.model.segment.action', MailVotech\LeadBundle\Model\SegmentActionModel::class);
    $services->alias('mailvotech.lead.model.ipaddress', MailVotech\LeadBundle\Model\IpAddressModel::class);
    $services->alias('mailvotech.lead.model.export_scheduler', MailVotech\LeadBundle\Model\ContactExportSchedulerModel::class);
    $services->alias('mailvotech.lead.repository.company', MailVotech\LeadBundle\Entity\CompanyRepository::class);
    $services->alias('mailvotech.lead.repository.company_lead', MailVotech\LeadBundle\Entity\CompanyLeadRepository::class);
    $services->alias('mailvotech.lead.repository.stages_lead_log', MailVotech\LeadBundle\Entity\StagesChangeLogRepository::class);
    $services->alias('mailvotech.lead.repository.dnc', MailVotech\LeadBundle\Entity\DoNotContactRepository::class);
    $services->alias('mailvotech.lead.repository.lead', MailVotech\LeadBundle\Entity\LeadRepository::class);
    $services->alias('mailvotech.lead.repository.list_lead', MailVotech\LeadBundle\Entity\ListLeadRepository::class);
    $services->alias('mailvotech.lead.repository.frequency_rule', MailVotech\LeadBundle\Entity\FrequencyRuleRepository::class);
    $services->alias('mailvotech.lead.repository.lead_event_log', MailVotech\LeadBundle\Entity\LeadEventLogRepository::class);
    $services->alias('mailvotech.lead.repository.lead_device', MailVotech\LeadBundle\Entity\LeadDeviceRepository::class);
    $services->alias('mailvotech.lead.repository.lead_list', MailVotech\LeadBundle\Entity\LeadListRepository::class);
    $services->alias('mailvotech.lead.repository.points_change_log', MailVotech\LeadBundle\Entity\PointsChangeLogRepository::class);
    $services->alias('mailvotech.lead.repository.merged_records', MailVotech\LeadBundle\Entity\MergeRecordRepository::class);
    $services->alias('mailvotech.lead.repository.field', MailVotech\LeadBundle\Entity\LeadFieldRepository::class);
    $services->alias('mailvotech.company.deduper', MailVotech\LeadBundle\Deduplicate\CompanyDeduper::class);
    $services->alias('mailvotech.lead.helper.contact_request_helper', MailVotech\LeadBundle\Helper\ContactRequestHelper::class);
    $services->alias('mailvotech.lead.helper.dnc_formatter_helper', MailVotech\LeadBundle\Helper\DncFormatterHelper::class);
    $services->alias('mailvotech.tracker.contact', MailVotech\LeadBundle\Tracker\ContactTracker::class);
    $services->alias('mailvotech.lead.field.settings.background_service', MailVotech\LeadBundle\Field\BackgroundService::class);
    $services->alias('mailvotech.lead.report.dnc_report_service', MailVotech\LeadBundle\Report\DncReportService::class);
    $services->alias('mailvotech.helper.segment.count.cache', MailVotech\LeadBundle\Helper\SegmentCountCacheHelper::class);
    $services->get(MailVotech\LeadBundle\Validator\Constraints\SegmentDateValidator::class)->tag('validator.constraint_validator');
};
