# User facing changes

*   {leadfield=...} tokens were removed. Use {contactfield=...} tokens instead.
*   Supported PHP versions are 7.2, 7.3 and 7.4
*   Console was moved to another directory. Update all your cron job and replace app/console with bin/console
*   There is a new file manager. You can see it when you go to a WYSIWYG editor and try to add an image for example.
*   The Rackspace and OpenStack plugins for remote assets were removed due to outdated library from Rackspace.

# New features

* The Integrations Bundle that was being developed in a separate repository ([https://github.com/mailvotech-inc/plugin-integrations](https://github.com/mailvotech-inc/plugin-integrations)) was moved into the core bundles into app/bundles directory. It is meant to replace the current way how integrations are being handled by the Plugin Bundle.
* MailVotech's Configuration is no longer dependent on Symfony's container. This means the container file does not have to be deleted and rebuilt after saving MailVotech's Configuration. However, this also means that any parameter that is used in a cache compiler pass is no longer compatible with the UI and must be manually configured in local.php. See Configuration below for more information and BC changes as a result.

# Backwards compatibility breaking changes

*   Minimal PHP version was increased from v5.6.19 to v7.2.21.
*   Minimal MySQL version was increased from v5.5.3 to v5.7.14
*   Symfony deprecations were removed or refactored [https://github.com/symfony/symfony/blob/3.0/UPGRADE-3.0.md](https://github.com/symfony/symfony/blob/3.0/UPGRADE-3.0.md)
*   Migrating the database should be done by upgrading to the latest 2.x series then to M3 as all 2.x migrations have been removed from the 3.x code
*   jQuery v2.x has been replaced with jQuery v3.3.1. jQuery 2.x code is not supported anymore..
*   app/version.txt has beeen removed in favor of app/release_metadata.json which includes the version
*   `\AppKernel::MAJOR_VERSION`, `\AppKernel::MINOR_VERSION`, `\AppKernel::PATCH_VERSION`, and `\AppKernel::EXTRA_VERSION` are no longer defined. Use `\MailVotech\CoreBundle\Release\ThisRelease::getMetadata()->getMajorVersion()`, etc instead. 

### Subscribers

*   Do not extend CommonSubscriber. It was removed. Use direct DI instead.
*   All protected properties and methods were made private. Subscribers should not be used to extend another ones. There are 2 exceptions. See bellow.
*   StatsSubscribers extend abstract class MailVotech\CoreBundle\EventListener\CommonStatsSubscriber. Those subscribers must have protected properties.
*   DashboardSubscribers extend abstract class MailVotech\DashboardBundle\EventListener\DashboardSubscriber\DashboardSubscriber. Those subscribers must have protected properties.
*   Subscribers that extend abstract class MailVotech\QueueBundle\EventListener\AbstractQueueSubscriber must keep protected properties.

### Configuration

In MailVotech 3, several configuration parameters have changed (`app/config/local.php`). The [upgrade script](https://github.com/mailvotech/mailvotech/issues/8819) will update those automatically for you, but here's an overview of parameters that will be updated by the script:

| Key | Old value | New value | Comment
|---|---|---|---|
| mailer_transport | 'mail' | 'sendmail' | 'mail' option was removed in SwiftMailer 6, other options should keep working as-is
| system_update_url | 'https://updates.mailvotech.org/ index.php?option=com_mailvotechdownload& task=checkUpdates' | 'https://api.github.com/ repos/mailvotech/mailvotech/releases' | New update mechanism
| dev_hosts | null | array() | If this was set to null, it should now be an empty array
| theme | 'Mauve' | 'blank' | Mauve theme was removed in 3.x (already deprecated for a while)
| track_by_fingerprint | 0 | N/A | Functionality removed in 3.x
| webhook_start | '0' | N/A | Removed in 3.x
| cache_path | 'YOUR_MAILVOTECH_FOLDER/ app/cache' | 'YOUR_MAILVOTECH_FOLDER/ app/../var/cache' | We'll only change the default value (new location is var/cache) but leave any custom configs intact
| log_path | 'YOUR_MAILVOTECH_FOLDER/ app/logs' | 'YOUR_MAILVOTECH_FOLDER/ app/../var/logs' | We'll only change the default value (new location is var/logs) but leave any custom configs intact
| tmp_path | 'YOUR_MAILVOTECH_FOLDER/ app/cache' | 'YOUR_MAILVOTECH_FOLDER/ app/../var/tmp' | We'll only change the default value (new location is var/tmp, it has its own dedicated folder now) but leave any custom configs intact
| mailer_spool_path | '%kernel.root_dir%/spool' | '%kernel.root_dir%/../var/spool' | We'll only change the default value (new location is var/spool) but leave any custom configs intact
| api_rate_limiter_cache | 'type' => 'file_system' | 'adapter' => 'cache.adapter.filesystem' | See below |

**Note on the `api_rate_limiter_cache` parameter**: this is an advanced feature that we only expect a very small subset of users to be using currently. 

If you had a custom API rate limiter other than the filesystem (default), you'll need to specify a [Symfony cache adapter](https://symfony.com/doc/3.4/cache.html#configuring-cache-with-frameworkbundle) as of MailVotech 3. **The upgrade script will change it to 'cache.adapter.filesystem' to prevent issues in the upgrade process, so you should change it yourself post-upgrade**.  For example, if you had Memcached set up with the following configuration:

```PHP
'api_rate_limiter_cache' => array(
  'memcached' => array(
    'servers' => array(
      '0' => array(
        'host' => 'memcached.local',
          'port' => '12345'
      )
    )
  )
),
```
... it should now be:

```PHP
'api_rate_limiter_cache' => array(
  'adapter' => 'cache.adapter.memcached',
  'provider' => 'memcached://memcached.local:12345'
),
```

In case you've been using `$_SERVER['MAILVOTECH_DEV_HOSTS']` to add more allowed IP addresses for the development enviroment, there had to be 2 changes because it was conflicting with handling configuration with environment variables.
1. Change `MAILVOTECH_DEV_HOSTS` to `MAILVOTECH_CUSTOM_DEV_HOSTS`.
2. It's not a string anymore, but a JSON encoded array.

Before:
```
$_SERVER['MAILVOTECH_DEV_HOSTS'] = '1.2.3.4';
```

After:
```
$_SERVER['MAILVOTECH_CUSTOM_DEV_HOSTS'] = '["1.2.3.4"]';
```

#### Removing the Container as a Configuration Dependency
MailVotech's Configuration is no longer dependent on the Symfony container. This means that any parameter that has to be used in a cache compiler pass is no longer compatible with the UI. These "advanced" configuration options have to be manually set in the local.php file then delete MailVotech's cache. For example, the QueueBundle must now be manually configured due to its dependency on cache compilers.

The following changes were made to support this:
1. `%mailvotech.parameters%` is no longer available to services. Use the `CoreParametersHelper` instead.
2. `CoreParametersHelper` is now limited to _just_ MailVotech configuration parameters. It can no longer be used to fetch other Symfony parameters, `mailvotech.paths`, `mailvotech.supported_languages`, `mailvotech.bundles`, or `mailvotech.plugin.bundles`. Pass them as needed to the constructor of the service, use the BundleHelper, LanguageHelper, or the PathsHelper instead.
3. Email spooling was not compatible with dynamic environment variables. Thus a new service was created to delegate whether an email should be spooled or sent immediately.
4. MailVotech\CoreBundle\Helper\CacheHelper::clearCache(), clearContainerFile(), clearTranslationCache(), and clearRoutingCache() were removed.

### PHPUNIT tests

PHPUNIT was upgraded to version 7.5.0 which means that the namespaces changed. See [https://thephp.cc/news/2017/02/migrating-to-phpunit-6](https://thephp.cc/news/2017/02/migrating-to-phpunit-6) for more details. 

The database fixtures are being installed based on the class name instead of the file path. Example:

MailVotech 2 (how it was before):

```
$this->installDatabaseFixtures([dirname(__DIR__).'/../../../../../app/bundles/LeadBundle/DataFixtures/ORM/LoadLeadData.php']);
```

MailVotech 3 (how it should be now): 
```
$this->installDatabaseFixtures([\MailVotech\LeadBundle\DataFixtures\ORM\LeadFieldData::class]);
```
All fixtures must be defined as services in a bundle's config.php


### CoreBundle

*   Remove SchemaHelperFactory as deprecated use specific helper services
*   `spacer` form type was removed without a replacement (\MailVotech\CoreBundle\Form\Type\SpacerType)
*   `hidden_entity` form type was removed (\MailVotech\CoreBundle\Form\Type\HiddenEntityType)
*   MailVotechFactory no longer injected into CommonRepository classes thus CommonRepository::setFactory() and $this->factory removed
*   MailVotechFactory no longer made available to AbstractMailVotechMigration; use $this->container instead
*   Removed deprecated \MailVotech\CoreBundle\CoreEvents::CHANNEL_BROADCAST use ChannelEvents::CHANNEL_BROADCAST instead
*   Removed deprecated \MailVotech\CoreBundle\CoreEvents::MESSAGE_QUEUED use ChannelEvents::MESSAGE_QUEUED instead
*   Removed deprecated \MailVotech\CoreBundle\CoreEvents::PROCESS_MESSAGE_QUEUE use ChannelEvents::PROCESS_MESSAGE_QUEUE instead
*   Removed deprecated \MailVotech\CoreBundle\CoreEvents::PROCESS_MESSAGE_QUEUE_BATCH use ChannelEvents::PROCESS_MESSAGE_QUEUE_BATCH instead
*   Removed deprecated \MailVotech\CoreBundle\Model\MessageQueueModel, use \MailVotech\ChannelBundle\Model\MessageQueueModel instead
*   Removed deprecated \MailVotech\CoreBundle\Controller\SubscribedEvents\BuilderTokenController 
*   Removed deprecated \MailVotech\CoreBundle\Entity\CommonRepository::validateDbalOrderByArray
*   Removed deprecated \MailVotech\CoreBundle\Entity\CommonRepository::validateDbalWhereArray
*   Removed deprecated \MailVotech\CoreBundle\Entity\CommonRepository::buildDbalOrderBy
*   Removed deprecated \MailVotech\CoreBundle\Entity\CommonRepository::buildDbalWhere
*   Removed deprecated \MailVotech\CoreBundle\Entity\CommonRepository::parseSearchFitlers
*   $allowVisualPlaceholder removed as second argument in \MailVotech\CoreBundle\Event\BuilderEvent::addTokens
*   $allowVisualPlaceholder removed as fifth argument from \MailVotech\CoreBundle\Event\BuilderEvent::addTokensFromHelper
*   Removed deprecated \MailVotech\CoreBundle\Event\BuilderEvent::addTokenSection
*   Removed deprecated \MailVotech\CoreBundle\Event\BuilderEvent::getTokenSections
*   Removed deprecated \MailVotech\CoreBundle\Event\BuilderEvent::getVisualTokens
*   Removed deprecated \MailVotech\CoreBundle\Event\BuilderEvent::tokenSectionsRequested
*   Removed deprecated \MailVotech\CoreBundle\Event\ChannelBroadcastEvent use \MailVotech\ChannelBundle\Event\ChannelBroadcastEvent instead
*   Removed deprecated \MailVotech\CoreBundle\Event\MessageQueueBatchProcessEvent use \MailVotech\ChannelBundle\Event\MessageQueueBatchProcessEvent instead
*   Removed deprecated \MailVotech\CoreBundle\Event\MessageQueueEvent use \MailVotech\ChannelBundle\Event\MessageQueueEvent instead
*   Removed deprecated \MailVotech\CoreBundle\Event\MessageQueueProcessEvent use \MailVotech\ChannelBundle\Event\MessageQueueProcessEvent instead
*   Removed deprecated \MailVotech\CoreBundle\Helper\AbstractFormFieldHelper::parseListStringIntoArray
*   Removed deprecated \MailVotech\CoreBundle\Helper\BuilderTokenHelper::replaceVisualPlaceholdersWithTokens
*   Removed deprecated \MailVotech\CoreBundle\Helper\BuilderTokenHelper::getTokenContent
*   Removed deprecated \MailVotech\CoreBundle\Security\Cryptography\Cipher\Symmetric\McryptCipher use \MailVotech\CoreBundle\Security\Cryptography\Cipher\Symmetric\OpenSSLCipher instead
*   \MailVotech\CoreBundle\Helper\CacheStorageHelper must specify an adaptor when instantiated
*   Support for deprecated permission prefix _plugin: _removed (i.e. plugin:focus:items:edit)
*   Removed deprecated \MailVotech\CoreBundle\Templating\Helper\FormatterHelper::getVersion use \MailVotech\CoreBundle\Templating\Helper\VersionHelper::getVersion instead
*   MailVotechJS.pixelLoaded() removed use MailVotechJS.onFirstEventDelivery() instead
*   Removed deprecated \MailVotech\CoreBundle\Templating\Helper\ButtonHelper::renderPreCustomButtons use renderButtons() instead
*   Removed deprecated \MailVotech\CoreBundle\Templating\Helper\ButtonHelper::renderPostCustomButtons
*   Removed deprecated \MailVotech\CoreBundle\Templating\Helper\ButtonHelper::setCustomButtons; listen to  CoreEvents::VIEW_INJECT_CUSTOM_BUTTONS event instead
*   Removed command class GenerateMigrationsCommand as it was broken with new Doctrine. Instead of `mailvotech:migrations:generate` use command `doctrine:migrations:generate` to generate a migration class.
*   Abstract class CommonStatsSubscriber was refactored out of CommonSubscriber and so it needs its own dependencies: CorePermissions and EntityManager. All classes extending CommonStatsSubscriber must provide those dependencies.
*   Protected methods CommonStatsSubscriber::addContactRestrictedRepositories() and CommonStatsSubscriber::addContactRestrictedRepositories() do not have the first parameter of EntityManager. See the point above. The second parameter must be an array now.
*   CommonSubscriber removed. Implement Symfony\Component\EventDispatcher\EventSubscriberInterface directly and use DI instead.
*   EventPass removed as it was not needed after the CommonSubscriber was removed.
*   BabDev/Transifex/* classes were replaced with MailVotech/Transifex/*
*   MailVotech\CoreBundle\IpLookup\MaxmindLookup was renamed to MailVotech\CoreBundle\IpLookup\AbstractMaxmindLookup and set as abstract class
*   MailVotech\CoreBundle\Form\DataTransformer\SortableListTransformer::__construct() has only 2 parameters now. The first one was removed as not used.
*   Fingerprint2 JS library was removed.
*   \MailVotech\CoreBundle\CoreParametersHelper can no longer be used to fetch other Symfony parameters, `mailvotech.paths`, `mailvotech.supported_languages`, `mailvotech.bundles`, or `mailvotech.plugin.bundles`. 
    * For Symfony parameters: Pass them as needed to the constructor of the service
    * For `mailvotech.bundles` or `mailvotech.plugin.bundles` use \MailVotech\CoreBundle\Helper\BundleHelper::getMailVotechBundles() or getPluginBundles() instead
    * For `mailvotech.supported_languages`, use \MailVotech\CoreBundle\Helper\LanguageHelper::getSupportedLanguages()
    * For `mailvotech.paths`, use \MailVotech\CoreBundle\Helper\PathsHelper::getSystemPath() instead
    * \MailVotech\CoreBundle\CoreParametersHelper::getParameter has been deprecated in favor of \MailVotech\CoreBundle\CoreParametersHelper::get()
*   \MailVotech\CoreBundle\Helper\UpdateHelper::getServerOs is now private
    
### ApiBundle

*   Removed MailVotechFactory, should use the service container and proper DI
*   Removed ‘error’ array element from API responses in favor of ‘errors’

### AssetBundle

*   Deprecated event AssetEvents::ASSET_ON_DOWNLOAD removed
*   Removed the now unused FormSubmitHelper 
*   AbTestHelper class removed, use DetermineWinnerSubscriber instead
*   Removed service mailvotech.form.type.asset_dashboard_downloads_in_time_widget with not existing class
*   Static method Asset::convertSizeToBytes() removed. Use MailVotech\CoreBundle\Helper\FileHelper::convertPHPSizeToBytes() instead.

### CampaignBundle
*   Deprecated LegacyEventDispatcher and legacy events removed:
    *   ON_EVENT_EXECUTION
    *   ON_EVENT_DECISION_TRIGGER
*   Deprecated classes removed:
    *   MailVotech/CampaignBundle/Entity/LegacyEventRepository 
    *   MailVotech\CampaignBundle/Event/CampaignDecisionEvent 
    *   MailVotech/CampaignBundle/Event/CampaignExecutionEvent 
    *   MailVotech/CampaignBundle/Event/CampaignScheduledEvent 
    *   MailVotech/CampaignBundle/Model/LegacyEventModel 
*   Deprecated methods removed:
    *   CampaignEventHelper::addRemoveLead()
    *   CampaignBuilderEvent::addLeadDecision(). Use CampaignBuilderEvent::addDecision() instead.
    *   CampaignBuilderEvent::getLeadDecisions(). Use CampaignBuilderEvent::getDecisions() instead.
    *   CampaignBuilderEvent::addLeadCondition(). Use CampaignBuilderEvent::addCondition() instead.
    *   CampaignBuilderEvent::getLeadConditions(). Use CampaignBuilderEvent::getConditions() instead.
    *   LeadRepository::getLeadsWithFields()
    *   CampaignRepository::getCampaignLeadIds()
    *   CampaignRepository::getCampaignLeadsFromLists()
    *   CampaignRepository::getCampaignOrphanLeads()
    *   LeadEventLogRepository::removeScheduledEvents()
    *   LeadRepository::getLeadsWithFields(). Use MailVotechLeadBundle\Entity\LeadRepository\getEntityContacts() instead.
    *   CampaignModel::removeScheduledEvents()
    *   CampaignModel::setChannelFromEventProperties(). Use \MailVotech\CampaignBundle\Helper\ChannelExtractor instead.
    *   CampaignModel::getRemovedLeads(). Use RemovedContactTracker::getRemovedContacts() instead.
    *   CampaignModel::addLead(). Use MembershipManager::addContact() instead.
    *   CampaignModel::addLeads(). Use MembershipManager::addContacts() instead.
    *   CampaignModel::getEvents(). Use EventCollector::getEvents() instead.
    *   CampaignModel::batchSleep()
    *   MailVotech\CampaignBundle\Executioner\Result\Responses::getResponseArray()
*   Deprecated tests removed:
    *   MailVotech/CampaignBundle/Tests/Executioner/Dispatcher/LegacyEventDispatcherTest
*   Commands
    *   Deprecated option `--negative-only` removed for command `mailvotech:campaigns:trigger`. Use `--inactive-only` instead.
*   Routes
    * `/api/campaigns/{id}/contact/add/{leadId}` route removed. Use `/api/campaigns/{id}/contact/{leadId}/add` instead.
    * `/api/campaigns/{id}/contact/remove/{leadId}` route removed. Use `/api/campaigns/{id}/contact/{leadId}/remove` instead.

### CategoryBundle

*   Deprecated class MailVotech\CategoryBundle\Helper\MenuHelper.php removed

### ChannelBundle

*   Removed MailVotech\ChannelBundle\ModelMessageQueueModel::dispatchDeprecatedEvent method
*   Removed MailVotech\ChannelBundle\ChannelEvents::ON_CAMPAIGN_TRIGGER_ACTION event
*   Removed MailVotech\LeadBundle\Event\ChannelEvent class. It's not in the ChannelBundle but it extends one of its classes and is deprecated.
*   Removed MailVotech\ChannelBundle\Event\ChannelEvent::setChannel and MailVotech\ChannelBundle\Event\ChannelEvent::getChannels

### ConfigBundle

*   Deprecated class ConfigModel removed
*   Deprecated JS function MailVotech.hideSpecificConfigFields removed
*   Deprecated method ConfigEvent::getPost() removed
*   1st param removed from constructor of ConfigBuilderEvent as it was never used.
All ConfigSubscribers must keep 'formAlias' but also add 'formType' with the form type class name.

Before:

```
public function onConfigGenerate(ConfigBuilderEvent $event)
{
    $event->addForm([
        'bundle'     => 'EmailBundle',
        'formAlias'  => 'emailconfig',
        'formTheme'  => 'MailVotechEmailBundle:FormTheme\Config',
        'parameters' => $event->getParametersFromConfig('MailVotechEmailBundle'),
    ]);
}
```

After:

```
public function onConfigGenerate(ConfigBuilderEvent $event)
{
    $event->addForm([
        'bundle'     => 'EmailBundle',
        'formType'   => ConfigType::class,
        'formAlias'  => 'emailconfig',
        'formTheme'  => 'MailVotechEmailBundle:FormTheme\Config',
        'parameters' => $event->getParametersFromConfig('MailVotechEmailBundle'),
    ]);
}
```

### CrmBundle

*   Deprecated method MailVotechPlugin\MailVotechCrmBundle\Integration\SalesforceIntegration::amendToSfFields removed
*   Deprecated method MailVotechPlugin\MailVotechCrmBundle\Integration\SugarcrmIntegration::amendToSfFields removed

### DashboardBundle

*   Class \MailVotech\DashboardBundle\EventListener\DashboardSubscriber that is used for extending by other DashboardSubscribers got rid of extending the CommonSubscriber. All dependencies handled by CommonSubscriber must be provided by direct DI now.

### DynamicContentBundle

*   BuildJsSubscriber had protected $formModel property which was not used. It was removed. All other protected properties were made private.

### EmailBundle

*   Deprecated route bc_mailvotech_api_sendcontactemail removed
*   Removed the now unused FormSubmitHelper 
*   Deprecated interface InterfaceCallbackTransport removed
*   Deprecated class MessageHelper removed
*   Deprecated method MailHelper::useMailerTokenization() removed
*   Deprecated method EmailController::exampleAction() removed
*   Deprecated method EmailModel::processMailerCallback() removed
*   Deprecated method EmailBundle::getVariants() removed
*   Deprecated method param $slots removed from SendEmailToContent::setEm… …
*   Deprecated method TransportType::getServiceRequiresLogin() removed
*   Deprecated method TransportType::getServiceDoNotNeedLogin() removed
*   EmailRepository::removeFromDoNotEmailList() removed
*   AbTestHelper class removed, use DetermineWinnerSubscriber instead
*   email.send.to.user action is now using  EmailEvents::ON_CAMPAIGN_BATCH_ACTION instead of EmailEvents::ON_CAMPAIGN_TRIGGER_ACTION

### EmailMarketingBundle

*   Classes that extend MailVotechPlugin\MailVotechEmailMarketingBundle\Integration\EmailAbstractIntegration class should implement getFormType. It should return either string (fully qualified class name  of a form type to use) or null.

### FormBundle

*   Class CalendarSubscriber got removed as the only subscribed event was commented out for 5 years and no one has missed it.
*   Dropped support for callbacks in form submit actions
*   Removed deprecated code to support callbacks
*   Removed the now unused FormSubmitHelper 

### HubspotApiFormBundle

*   AbTestHelper class removed, use DetermineWinnerSubscriber instead

### InstallBundle

*   Removed unused \MailVotech\InstallBundle\Configurator\Step\CheckStep::$install_source
*   Remove unused \MailVotech\InstallBundle\Configurator\Step\DoctrineStep::$server_version

### IntegrationsBundle

*   This bundle was not part of MailVotech, but if you've built some plugins using that bundle, make sure you update the namespaces from MailVotechPlugin/IntegrationsBundle/… to MailVotech/IntegrationsBundle/...

### LeadBundle

*   LeadModel Deprecated methods removed:
    * remove LeadModel::flattenFields use Lead::getProfileFields
    * remove LeadModel::getFrequencyRule and use LeadModel::getFrequencyRules
    * remove LeadModel::getAllChannels() use mailvotech.channel.helper.channel_list serviceremove LeadModel::getAllChannels() use mailvotech.channel.helper.channel_list service
    * remove reference to setSystemCurrentLead setCurrentUser use ContactTracker instead
    * deprecated LeadModel::isContactable use DoNotContact::isContactable
    * remove LeadModel::addDncForLead use DoNotContact::removeDncForContact
    * remove LeadModel::removeDncForLead use DoNotContact::addDncForContact
*   FieldToOrderTransformer depends on LeadFieldRepostitory instead of EntityManager.
*   Remove the now unused Event\EventHelper class
*   Remove the now unused Event\FormEventHelper class
*   Remove the now unused FormEvents::FORM_SUBMIT_REMOVE_DO_NO_CONTACT constant
*   Class StageActionType removed as it was not used anywhere. Not even in config.php.
*   Class LeadLookupListType removed as it was not used anywhere. Not even in config.php. It was clearly copy of CompanyListType that was never used.
*   Class ListTriggerType removed as it was not used anywhere.
*   Class PointTriggerType removed as it was not used anywhere.
*   Class TagListType removed as it was not used anywhere.
*   Class MailVotech\LeadBundle\Form\DataTransformer\UtmTagEntityModelTransformer removed as it was not used anywhere.
*   Property (column) LeadDevice::deviceFingerprint was removed together with its setter and getter.
*   Method LeadDeviceRepository::getDeviceByFingerprint() removed.
*   Deprecated class EmailTokenHelper removed.
*   Deprecated routes removed:
    *   mailvotech_contact_import_index - /s/{object}/import/{page}
    *   mailvotech_contact_import_action - /s/{object}/import/{objectAction}/{objectId}
    *   bc_mailvotech_api_segmentaddcontact - /api/segments/{id}/contact/add/{leadId}
    *   bc_mailvotech_api_segmentremovecontact - /api/segments/{id}/contact/remove/{leadId}
    *   bc_mailvotech_api_companyaddcontact - /api/companies/{companyId}/contact/add/{contactId}
    *   bc_mailvotech_api_companyremovecontact - /api/companies/{companyId}/contact/remove/{contactId}
    *   bc_mailvotech_api_dncaddcontact - /api/contacts/{id}/dnc/add/{channel}
    *   bc_mailvotech_api_dncremovecontact - /api/contacts/{id}/dnc/remove/{channel}
    *   bc_mailvotech_api_getcontactevents - /api/contacts/{id}/events
*   Deprecated method LeadApiController::getExistingLead() removed.
*   Deprecated method LeadApiController::getEventsAction() removed.
*   Deprecated properties manuallyRemoved and manuallyAdded in entity CompanyLead were removed. That is with with the database schema and getters and setters:
    *   getManuallyRemoved()
    *   setManuallyRemoved()
    *   wasManuallyRemoved()
    *   getManuallyAdded()
    *   setManuallyAdded()
    *   wasManuallyAdded()
*   Deprecated Lead entity changes “dnc_status” was removed. Use “dnc_channel_status” instead.
*   Deprecated methods in the LeadDevice entity getDateOpen() and setDateOpen() were removed.
*   Deprecated methods in the LeadEventLogRepository class getEventsByLead() and getEventsByAction() were removed.
*   Deprecated method LeadListRepository::getLeadsByList() was removed.
*   Methods LeadListRepository::generateSegmentExpression() and LeadListRepository::generateSegmentExpression() were removed as they were not being called from anywhere, only from each other.
*   Deprecated method PointsChangeLogRepository::countValue() was removed.
*   Deprecated methods in the StagesChangeLogRepository class getMostStages(), getMostLeads() and countValue() were removed.
*   Deprecated class ChannelEvent removed.
*   Deprecated static method FormFieldHelper::getTimezonesChoices() was removed.
*   Token prefix leadfield= won’t be processed anymore. Use contactfield instead.
*   Deprecated constant LeadEvents::ADD_CHANNEL removed.
*   Deprecated method FieldModel::getUniqueIdentiferFields() was removed.
*   Deprecated method ImportModel::startImport() was removed. Use beginImport instead.
*   Deprecated methods in the LeadModel class getLeadFromRequest(), importLead(), setLeadCookie() and getTrackingCookie() were removed.
*   Deprecated method ListModel::getLeadsByList() was removed.
*   Signature of ListModel::getLifeCycleSegments() method changed. 4th param is not array of $filters but bool of $canViewOthers. That method was actually called with bool anyway.
*   Signature of ListModel::getTopLists() method changed. 3th param is not array of $filters but bool of $canViewOthers. That method was actually called with bool anyway.
*   Signature of ContactRequestHelper::__construct() method changed. 5th param removed.
*   Deprecated method DateDecorator::getDefaultDate() was removed.
*   Deprecated cookie mailvotech_session_id will no longer be created.
*   Payloads for the following webhook events will no longer have the lead array element: Contact Deleted Event, Contact Points Changed Event, Contact Updated Event. “lead” array element has been removed. We have the same information in the contact array element.
*   LeadEvents::FORM_SUBMIT_REMOVE_DO_NOT_CONTACT event is removed. Listen for the LeadEvents::ON_EXECUTE_SUBMIT_ACTION instead, and check the context of `lead.remove_do_not_contact`
*   3rd param $identifier of Constructor of MailVotech\LeadBundle\Form\DataTransformer\TagEntityModelTransformer removed as it was not used.
*   Entity\LeadListRepository::getLists() signature changed to accept MailVotech\UserBundle\Entity\User or null as first argument

### NotificationsBundle

*   Remove the unused and non-funcational FormSubscriber

### PageBundle

*   AbTestHelper class removed, use DetermineWinnerSubscriber instead
*   Removed MailVotech\PageBundle\Form\Type\SlideshowGlobalConfigType
*   Removed MailVotech\PageBundle\Form\Type\SlideshowSlideConfigType

### PluginBundle

*   Removed deprecated method MailVotech\PluginBundle\Controller\AjaxController::getIntegrationLeadFieldsAction()
*   Removed deprecated method MailVotech\PluginBundle\Controller\AjaxController::getIntegrationCompanyFieldsAction()
*   Removed deprecated method MailVotech\PluginBundle\Integration\AbstractIntegration::init()
*   Removed deprecated method MailVotech\PluginBundle\Integration\AbstractIntegration::getUserId()
*   Changed constructor args in \MailVotech\PluginBundle\Integration\AbstractIntegration and removed setter methods used by IntegrationPass
*   Removed \CoreBundle\DependencyInjection\Compiler\IntegrationPass
*   Removed \PluginBundle\Controller\AjaxController::getIntegrationLeadFieldsAction()
*   Removed \PluginBundle\Controller\AjaxController::getIntegrationCompanyFieldsAction()
*   Removed constructor from \MailVotech\PluginBundle\Form\Type\FieldsType
*   Removed constructor from \MailVotech\PluginBundle\Form\Type\CompanyFieldsType

### SMSBundle

*   \MailVotech\SmsBundle\Form\Type\SmsType::__constructor() removed
*   Remove unused MailVotech\SmsBundle\Event\SmsClickEvent
*   Remove unused \MailVotech\SmsBundle\Exception\MissingPasswordException
*   Remove unused \MailVotech\SmsBundle\Exception\MissingUsernameException

### StageBundle

*   Deprecated route bc_mailvotech_api_stageddcontact removed
*   Deprecated route bc_mailvotech_api_stageremovecontact removed

### SocialBundle

*   Deprecated method MailVotechPlugin\MailVotechSocialBundle\Command\MonitorTwitterBaseCommand::createLeadsFromStatuses removed
*   Deprecated method MailVotechPlugin\MailVotechSocialBundle\Command\MonitorTwitterBaseCommand::getTwitterIntegration removed
*   Deprecated method MailVotechPlugin\MailVotechSocialBundle\Command\MonitorTwitterBaseCommand::buildTwitterSearchQueryy removed
*   Classes GooglePlusType and GooglePlusIntegration removed as Google+ social network does not exist anymore.

### UserBundle

*   Deprecated magic method User::__get() removed

### WebhookBundle 

*   Removed class MailVotech\WebhookBundle\EventListener\WebhookSubscriberBase
*   & not used for first parameter in constructor anymore in \MailVotech\WebhookBundle\Event\WebhookEvent
*   Removed MailVotech\WebhookBundle\Model\WebhookModel::webhookStart (protected property)
*   \MailVotech\WebhookBundle\Form\Type\WebhookType notranslator in constructor
*   Removed WebhookBundle\EventListener\WebhookModelTrait
*   Payloads for the following webhook events will no longer have the lead array element: Contact Deleted Event, Contact Points Changed Event, Contact Updated Event and Contact Identified Event. “lead” array element has been removed. We have the same information in the contact array element.

### MailVotechCloudStorageBundle

*   Abstract class CloudStorageIntegration has new abstract method getForm().
*   The Rackspacke and OpenStack Integrations were removed. That includes these classes:
    *   MailVotechPlugin\MailVotechCloudStorageBundle\Form\Type\OpenStackType
    *   MailVotechPlugin\MailVotechCloudStorageBundle\Form\Type\RackspaceType
    *   MailVotechPlugin\MailVotechCloudStorageBundle\Integration\OpenStackIntegration
    *   MailVotechPlugin\MailVotechCloudStorageBundle\Integration\RackspaceIntegration

### MailVotechCitrixBundle

*   FormSubscriber::onResponse() method was empty and removed.
*   LeadSubscriber::onListOperatorsGenerate() method was empty and removed.

### MailVotechFullContactBundle

*   Exceptions were renamed. 
    *   MailVotechPlugin\MailVotechFullContactBundle\Exception\API was renamed to ApiException
    *   MailVotechPlugin\MailVotechFullContactBundle\Exception\NoCredit was renamed to NoCreditException
    *   MailVotechPlugin\MailVotechFullContactBundle\Exception\NotImplemented was renamed to NotImplementedException
    *   MailVotechPlugin\MailVotechFullContactBundle\Exception\Base was renamed to BaseException

# Other

*   Form type classes getName() methods should be renamed (not removed) to getBlockPrefix() method to save some issues with JS files which use form input ID selectors.
*   `setOptional` method in form types is deprecated. Please use `setDefined` method instead.
*   choice_list must be changed to choices, and the value of class ChoiceType has to be replaced with choices and an array with _labels_ as keys; there’s a bug in Symfony 2.8 that you also have to set ‘choices_as_values’ => true to ensure that labels and values are correct. 
*   Some use of choices for ChoiceType in MailVotech 2 is dependent on the Symfony 2.8 bc break and need to have the key/values flipped. See [https://symfony.com/doc/2.8/reference/forms/types/choice.html#choices-as-values](https://symfony.com/doc/2.8/reference/forms/types/choice.html#choices-as-values)
*   $view[‘router’]->generate() in PHP templates has to be changed to $view[‘router’]->url()
*   [sensio/generator-bundle](https://packagist.org/packages/sensio/generator-bundle) package was removed from the dev dependencies as it's abandoned.
*   [symfony/browser-kit](https://symfony.com/doc/current/components/browser_kit.html) package was moved from the prod into dev dependencies as it's supposed to be used for functional tests.
*   [symfony/dom-crawler](https://symfony.com/doc/current/components/dom_crawler.html) package was moved from the prod into dev dependencies as it's supposed to be used for functional tests.
