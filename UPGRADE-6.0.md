# Backwards compatibility breaking changes

## Removed features
- The Gated Video feature was removed as it was used only in the Legacy builder. See https://github.com/mailvotech/mailvotech/pull/14284
- The Froala editor got removed due to security issues of the old version we couldn't update due to licencing issues. It was used in the legacy builder only.

## BC breaks in the code

### Javascript

As the legacy builder was removed these JS libraries were removed as well:
- Froala (outdated with security vulnerabilities)
- CodeMirror JS (still installed in the GrapesJS plugin, but not part of MailVotech itself)
- Jquery UI - Safe Blur
- Modernizr as not necessary anymore as the modern browsers support open standards

### PHP
- Multiple method signatures changed to improve type coverage. Some forced by dependency updates, some in MailVotech itself. Run `composer phpstan` when your plugin is installed to get the full list related to your plugin.
- `MailVotech\PointBundle\Form\Type\GenericPointSettingsType` was removed. See https://github.com/mailvotech/mailvotech/pull/13904
- Changes necessary for https://symfony.com/blog/new-in-symfony-5-3-guard-component-deprecation, see https://github.com/mailvotech/mailvotech/pull/14219
    - `MailVotech\ApiBundle\DependencyInjection\Factory\ApiFactory` was removed.
    - The `friendsofsymfony/oauth-server-bundle` package was replaced with a maintained fork `klapaudius/oauth-server-bundle`
    - The `lightsaml/sp-bundle` package was replaced with a maintained fork `javer/sp-bundle`
- Deprecated `MailVotech\LeadBundle\Model\FieldModel::getUniqueIdentiferFields` and `MailVotech\LeadBundle\Model\FieldModel::getUniqueIdentifierFields` were removed. Use `MailVotech\LeadBundle\Field\FieldsWithUniqueIdentifier::getFieldsWithUniqueIdentifier` instead.
- The signature for the `MailVotech\PluginBundle\Integration\AbstractIntegration::__construct()` had to be changed as the `SessionInterface` service no longer exists in Symfony 6. So it was removed from the constructor and session is being fetched from the `RequestStack` instead.
- Removed `MailVotech\CoreBundle\Factory\MailVotechFactory::getRequest` use dependency injection with RequestStack instead.
- Removed PluginBundleBase::onPluginInstall, listen to the PluginEvents::ON_PLUGIN_INSTALL instead.
- Removed PluginBundleBase::onPluginUpdate, listen to the PluginEvents::ON_PLUGIN_UPDATE instead.
- Moved PluginBundleBase::installPluginSchema to \MailVotech\PluginBundle\Bundle\PluginDatabase::installPluginSchema. MailVotechFactory is removed as parameter.
- Removed PluginBundleBase::updatePluginSchema, as method was not recommended, and produced bad results.
- Removed PluginBundleBase::onPluginUninstall, as method was empty.
- Moved PluginBundleBase::dropPluginSchema to \MailVotech\PluginBundle\Bundle\PluginDatabase::dropPluginSchema. Removed MailVotechFactory as parameter.
- Removed AbstractPluginBundle::onPluginUpdate, now listening to the PluginEvents::ON_PLUGIN_UPDATE.
- Removed `MailVotech\CoreBundle\Factory\MailVotechFactory::getDatabase` use dependency injection instead.
- Removed `MailVotech\CoreBundle\Factory\MailVotechFactory::getHelper` use dependency injection instead.
- Removed `MailVotech\CoreBundle\Factory\MailVotechFactory::getDebugMode` use dependency injection instead.
- Removed `MailVotech\CoreBundle\Factory\MailVotechFactory::getMailVotechBundles` use BundleHelper instead.
- Removed `MailVotech\CoreBundle\Factory\MailVotechFactory::getKernel` use dependency injection instead.
- Removed `MailVotech\CoreBundle\Factory\MailVotechFactory::getParameter` use DI with the `\MailVotech\CoreBundle\Helper\CoreParametersHelper` instead.
- Removed `MailVotech\CoreBundle\Factory\MailVotechFactory::getVersion` use dependency injection with KernelInterface, which will retrieve \AppKernel, then invoke getVersion() method.
- Removed `MailVotech\CoreBundle\Factory\MailVotechFactory::getPluginBundles` use BundleHelper instead.
- Removed `MailVotech\CoreBundle\Factory\MailVotechFactory::getBundleConfig` use BundleHelper instead.
- Removed `MailVotech\CoreBundle\Factory\MailVotechFactory::getUser` use UserHelper instead.
- Removed `MailVotech\CoreBundle\Factory\MailVotechFactory::getSystemPath` use PathsHelper instead.
- Removed `MailVotech\CoreBundle\Factory\MailVotechFactory::getTranslator` use Translator instead.
- Removed `MailVotech\CoreBundle\Factory\MailVotechFactory::getRouter` use Router or UrlGeneratorInterface instead.
- Removed `MailVotech\CoreBundle\Factory\MailVotechFactory::getLocalConfigFile` use dependency injection with KernelInterface, which will retrieve \AppKernel, then invoke getLocalConfigFile().
- Removed `MailVotech\CoreBundle\Factory\MailVotechFactory::getEnvironment` use dependency injection instead.
- Removed `MailVotech\CoreBundle\Factory\MailVotechFactory::getIpAddress` use IpLookupHelper instead.
- Removed `MailVotech\CoreBundle\Factory\MailVotechFactory::getSecurity` use dependency injection instead.
- Removed `MailVotech\CoreBundle\Factory\MailVotechFactory::get` use dependency injection instead.
- Removed `MailVotech\CoreBundle\Factory\MailVotechFactory::serviceExists` use dependency injection instead.
- Removed `MailVotech\CoreBundle\Factory\MailVotechFactory::getSecurityContext` use dependency injection instead.
- Removed `MailVotech\CoreBundle\Factory\MailVotechFactory::getDispatcher` use dependency injection instead.
- Removed `MailVotech\CoreBundle\Factory\MailVotechFactory::getMailer` use dependency injection instead with \MailVotech\EmailBundle\Helper\MailHelper.
- Removed `MailVotech\CoreBundle\Factory\MailVotechFactory::getIpAddressFromRequest` use dependency injection with \MailVotech\CoreBundle\Helper\IpLookupHelper instead.
- Removed `MailVotech\CoreBundle\Factory\MailVotechFactory::getDate` use \MailVotech\CoreBundle\Helper\DateTimeHelper instead.
- Removed `MailVotech\CoreBundle\Factory\MailVotechFactory::getLogger` use dependency injection instead.
- Removed `MailVotech\CoreBundle\Factory\MailVotechFactory::getTwig` use DI with the `\Twig\Environment` instead.
- Removed `MailVotech\CoreBundle\Factory\MailVotechFactory::getTheme` use DI with the `\MailVotech\CoreBundle\Helper\ThemeHelper` instead.
- Removed `MailVotech\CoreBundle\Factory\MailVotechFactory::getInstalledThemes` use DI with the `\MailVotech\CoreBundle\Helper\ThemeHelper` instead.
- Removed `MailVotech\CoreBundle\Factory\MailVotechFactory::getEntityManager` use dependency injection instead.
- Removed `MailVotech\CoreBundle\Factory\MailVotechFactory::getModel` use dependency injection instead. Quick replacement will be `MailVotech\CoreBundle\Factory\ModelFactory::getModel`, but most sustainable is to use dependency injection.
- Removed `MailVotech\CoreBundle\Factory\MailVotechFactory`, `'mailvotech.factory'` service.
- Removed `MailVotech\CampaignBundle\Entity::getEventsByChannel()` as unused and buggy. No replacement
- Removed `MailVotech\CoreBundle\Test::createAnotherClient()` as unused. No replacement.
- Removed `MailVotech\NotificationBundle\Entity::getLeadStats()` as unused and buggy. No replacment
- Removed `MailVotech\WebhookBundle\Entity::removeOldLogs()` as it was deprecated. Use `removeLimitExceedLogs()` instead.
- Removed `MailVotech\PageBundle\Entity::findByIds()` as unused and buggy. Use Doctrine's `findAllBy(['id' => [1,2]])` instead.
- Removed `MailVotech\PluginBundle\Controller::getIntegrationCampaignsAction()` as unused and buggy together with JS function `MailVotech.getIntegrationCampaigns`
- Removed `MailVotech\CoreBundle\Tests\Functional\Service::class` as unused and testing 3rd party code instead of MailVotech.
- Removed `MailVotech\CoreBundle\Doctrine\TranslationMigrationTrait` as unused and deprecated.
- Removed `MailVotech\CoreBundle\Doctrine\VariantMigrationTrait` as unused and deprecated.
- Removed `MailVotech\IntegrationsBundle\Form\Type\NotBlankIfPublishedConstraintTrait` as unused.
- Removed `MailVotech\IntegrationsBundle\Form\Type\Auth\BasicAuthKeysTrait` as unused.
- Removed `MailVotech\IntegrationsBundle\Form\Type\Auth\Oauth1aTwoLeggedKeysTrait` as unused.
- Removed `MailVotech\CoreBundle\Helper\CoreParametersHelper::getParameter()`. Use `MailVotech\CoreBundle\Helper\CoreParametersHelper::get()` instead.
- Removed these services as the authentication system in Symfony 6 has changed and these services were using code that no longer existed.
    - `mailvotech.user.form_guard_authenticator` (`MailVotech\UserBundle\Security\Authenticator\FormAuthenticator::class`)
    - `mailvotech.user.preauth_authenticator` (`MailVotech\UserBundle\Security\Authenticator\PreAuthAuthenticator::class`)
    - `mailvotech.security.authentication_listener` (`MailVotech\UserBundle\Security\Firewall\AuthenticationListener::class`)
- The `GrapesJsData` class was moved from `MailVotech\InstallBundle\InstallFixtures\ORM` namespace to `MailVotechPlugin\GrapesJsBuilderBundle\InstallFixtures\ORM` as plugins should not be coupled with core bundles.
- The `lightsaml/sp-bundle` package was replaced with a maintained fork `lightsaml2/sp-bundle`
- `MailVotech\PageBundle\Form\Type\PagePublishDatesType` was removed.
- `getSessionName` was removed from `MailVotech\PageBundle\Helper\TrackingHelper` No session for anonymous users. Use `getCacheKey`.
- `getSession` was removed from `MailVotech\PageBundle\Helper\TrackingHelper` No session for anonymous users. Use `getCacheItem`.
- `updateSession` was removed from `MailVotech\PageBundle\Helper\TrackingHelper` No session for anonymous users. Use `updateCacheItem`.
- `getNewVsReturningPieChartData` was removed from `MailVotech\PageBundle\Model\PageModel`. Use `getUniqueVsReturningPieChartData()` instead.
- `MailVotech\PageBundle\Helper\PointActionHelper::validateUrlHit` is no longer static.
- Replaced the `tightenco/collect:^8.16.0` package with `illuminate/collections:^10.48`.
- Form submissions now store data without HTML entity encoding instead of with encoded entities (e.g., `R&R` instead of `R&#x26;R`)
- `FormFieldHelper::getTypes` signature has been changed
- `FormFieldHelper::getFieldFilter` signature has been changed and now returns `string` filter by default

## Most notable changes required by Symfony 6

### Getting a value from request must be scalar

Meaning arrays cannot be returned with the `get()` method. Example of how to resolve it:
```diff
- $asset = $request->request->get('asset') ?? [];
+ $asset = $request->request->all()['asset'] ?? [];
```

### ASC contants replaced with enums in Doctrine
```diff
- $q->orderBy($this->getTableAlias().'.dateAdded', \Doctrine\Common\Collections\Criteria::DESC);
+ $q->orderBy($this->getTableAlias().'.dateAdded', \Doctrine\Common\Collections\Order::Descending->value);
```

### Creating AJAX requests in functional tests
```diff
- $this->client->request(Request::METHOD_POST, '/s/ajax', $payload, [], $this->createAjaxHeaders());
+ $this->setCsrfHeader(); // this is necessary only for the /s/ajax endpoints. Other ajax requests do not need it.
+ $this->client->xmlHttpRequest(Request::METHOD_POST, '/s/ajax', $payload);
```

### Logging in different user in functional tests
```diff
- $user = $this->loginUser('admin');
+ $user = $this->em->getRepository(User::class)->findOneBy(['username' => 'admin']);
+ $this->loginUser($user);
```

### Asserting successful response in functional tests
```diff
$this->client->request('GET', '/s/campaigns/new/');
- $response = $this->client->getResponse();
- Assert::assertTrue($response->isOk(), $response->getContent());
+ $this->assertResponseIsSuccessful();
```

### Session service doesn't exist anymore
Use Request to get the session instead.
```diff
- use Symfony\Component\HttpFoundation\Session\SessionInterface;
+ use Symfony\Component\HttpFoundation\RequestStack;
class NeedsSession
{
-   public function __construct(private SessionInterface $session) {}
+   public function __construct(private RequestStack $requestStack) {}

    public function doStuff()
    {
-       $selected = $this->session->get('mailvotech.category.type', 'category');
+       $selected = $this->requestStack->getSession()->get('mailvotech.category.type', 'category');
        // ...
    }
}
```

# Notes

- Migration file `app/migrations/Version20230522141144.php` has been removed. If you do not use the MailVotech Citrix plugin or a fork of it, you can manually drop the `plugin_citrix_events` table from the database, as it is no longer used.