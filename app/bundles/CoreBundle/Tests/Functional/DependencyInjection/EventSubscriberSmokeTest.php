<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Tests\Functional\DependencyInjection;

use MailVotech\ApiBundle\EventListener\ApiSubscriber;
use MailVotech\CampaignBundle\EventListener\CampaignActionChangeMembershipSubscriber;
use MailVotech\CampaignBundle\EventListener\CampaignActionJumpToEventSubscriber;
use MailVotech\CoreBundle\EventListener\AssetsSubscriber;
use MailVotech\CoreBundle\EventListener\ConfigThemeSubscriber;
use MailVotech\CoreBundle\EventListener\CoreSubscriber;
use MailVotech\CoreBundle\EventListener\EnvironmentSubscriber;
use MailVotech\CoreBundle\EventListener\ErrorHandlingListener;
use MailVotech\CoreBundle\EventListener\ExceptionListener;
use MailVotech\CoreBundle\EventListener\RequestSubscriber;
use MailVotech\CoreBundle\EventListener\RouterSubscriber;
use MailVotech\CoreBundle\Helper\CookieHelper;
use MailVotech\EmailBundle\EventListener\DateTimeTokenSubscriber;
use MailVotech\EmailBundle\EventListener\PointSubscriber;
use MailVotech\EmailBundle\EventListener\ProcessUnsubscribeSubscriber;
use MailVotech\EmailBundle\EventListener\TokenSubscriber;
use MailVotech\FormBundle\EventListener\FormValidationSubscriber;
use MailVotech\IntegrationsBundle\EventListener\ControllerSubscriber;
use MailVotech\LeadBundle\EventListener\CampaignActionDeleteContactSubscriber;
use MailVotech\LeadBundle\EventListener\CampaignActionDNCSubscriber;
use MailVotech\LeadBundle\EventListener\OwnerSubscriber;
use MailVotech\LeadBundle\EventListener\ReportDevicesSubscriber;
use MailVotech\LeadBundle\EventListener\ReportDNCSubscriber;
use MailVotech\LeadBundle\EventListener\ReportUtmTagSubscriber;
use MailVotech\LeadBundle\EventListener\SegmentLogReportSubscriber;
use MailVotech\LeadBundle\EventListener\SegmentReportSubscriber;
use MailVotech\SmsBundle\EventListener\CampaignReplySubscriber;
use MailVotech\SmsBundle\EventListener\CampaignSendSubscriber;
use MailVotech\UserBundle\Controller\SecurityController;
use MailVotech\UserBundle\EventListener\ApiUserSubscriber;
use MailVotech\UserBundle\EventListener\LogoutListener;
use MailVotech\UserBundle\EventListener\PasswordStrengthSubscriber;
use MailVotech\UserBundle\EventListener\PasswordSubscriber;
use MailVotechPlugin\MailVotechFocusBundle\EventListener\FocusSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\AuthenticationTokenCreatedEvent;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;

final class EventSubscriberSmokeTest extends AbstractContainerSmokeTestCase
{
    /**
     * There are 300 local event subscribers in the container, keep a small reserve for removed ones.
     */
    private const MINIMAL_EVENT_SUBSCRIBER_COUNT = 297;

    /**
     * Local event subscribers per hand-picked event, to catch a subscriber that silently stops listening.
     *
     * @var array<string, string[]>
     */
    private const EXPECTED_EVENT_SUBSCRIBER_CLASSES = [
        LogoutEvent::class => [
            LogoutListener::class,
        ],
        CheckPassportEvent::class => [
            ApiUserSubscriber::class,
            PasswordStrengthSubscriber::class,
            PasswordSubscriber::class,
        ],
        AuthenticationTokenCreatedEvent::class => [
            ApiUserSubscriber::class,
        ],
        'kernel.request' => [
            FocusSubscriber::class,
            ApiSubscriber::class,
            AssetsSubscriber::class,
            EnvironmentSubscriber::class,
            ErrorHandlingListener::class,
            RequestSubscriber::class,
            RouterSubscriber::class,
            SecurityController::class,
        ],
        'kernel.response' => [
            ApiSubscriber::class,
            ExceptionListener::class,
            CookieHelper::class,
        ],
        'kernel.exception' => [
            ExceptionListener::class,
        ],
        'kernel.controller' => [
            ControllerSubscriber::class,
        ],
        \FOS\OAuthServerBundle\Event\OAuthEvent::PRE_AUTHORIZATION_PROCESS => [
            \MailVotech\ApiBundle\EventListener\PreAuthorizationEventListener::class,
        ],
        \FOS\OAuthServerBundle\Event\OAuthEvent::POST_AUTHORIZATION_PROCESS => [
            \MailVotech\ApiBundle\EventListener\PreAuthorizationEventListener::class,
        ],
        'security.interactive_login' => [
            CoreSubscriber::class,
        ],
        'mailvotech.campaign_on_build' => [
            \MailVotechPlugin\MailVotechFocusBundle\EventListener\CampaignSubscriber::class,
            \MailVotechPlugin\MailVotechSocialBundle\EventListener\CampaignSubscriber::class,
            \MailVotech\AssetBundle\EventListener\CampaignSubscriber::class,
            CampaignActionChangeMembershipSubscriber::class,
            CampaignActionJumpToEventSubscriber::class,
            \MailVotech\ChannelBundle\EventListener\CampaignSubscriber::class,
            \MailVotech\DynamicContentBundle\EventListener\CampaignSubscriber::class,
            \MailVotech\EmailBundle\EventListener\CampaignConditionSubscriber::class,
            \MailVotech\EmailBundle\EventListener\CampaignSubscriber::class,
            \MailVotech\FormBundle\EventListener\CampaignSubscriber::class,
            CampaignActionDNCSubscriber::class,
            CampaignActionDeleteContactSubscriber::class,
            \MailVotech\LeadBundle\EventListener\CampaignSubscriber::class,
            \MailVotech\NotificationBundle\EventListener\CampaignConditionSubscriber::class,
            \MailVotech\NotificationBundle\EventListener\CampaignSubscriber::class,
            \MailVotech\PageBundle\EventListener\CampaignSubscriber::class,
            \MailVotech\PluginBundle\EventListener\CampaignSubscriber::class,
            CampaignReplySubscriber::class,
            CampaignSendSubscriber::class,
            \MailVotech\StageBundle\EventListener\CampaignSubscriber::class,
            \MailVotech\WebhookBundle\EventListener\CampaignSubscriber::class,
        ],
        'mailvotech.report_on_build' => [
            FocusSubscriber::class,
            \MailVotechPlugin\MailVotechFocusBundle\EventListener\ReportSubscriber::class,
            \MailVotech\AssetBundle\EventListener\ReportSubscriber::class,
            \MailVotech\CampaignBundle\EventListener\ReportSubscriber::class,
            \MailVotech\ChannelBundle\EventListener\ReportSubscriber::class,
            \MailVotech\CoreBundle\EventListener\ReportSubscriber::class,
            \MailVotech\EmailBundle\EventListener\ReportSubscriber::class,
            \MailVotech\FormBundle\EventListener\ReportSubscriber::class,
            ReportDNCSubscriber::class,
            ReportDevicesSubscriber::class,
            \MailVotech\LeadBundle\EventListener\ReportSubscriber::class,
            ReportUtmTagSubscriber::class,
            SegmentLogReportSubscriber::class,
            SegmentReportSubscriber::class,
            \MailVotech\NotificationBundle\EventListener\ReportSubscriber::class,
            \MailVotech\PageBundle\EventListener\ReportSubscriber::class,
            \MailVotech\PointBundle\EventListener\ReportSubscriber::class,
        ],
        'mailvotech.config_on_generate' => [
            \MailVotechPlugin\MailVotechSocialBundle\EventListener\ConfigSubscriber::class,
            \MailVotech\ApiBundle\EventListener\ConfigSubscriber::class,
            \MailVotech\AssetBundle\EventListener\ConfigSubscriber::class,
            \MailVotech\CampaignBundle\EventListener\ConfigSubscriber::class,
            \MailVotech\CoreBundle\EventListener\ConfigSubscriber::class,
            ConfigThemeSubscriber::class,
            \MailVotech\EmailBundle\EventListener\ConfigSubscriber::class,
            \MailVotech\FormBundle\EventListener\ConfigSubscriber::class,
            \MailVotech\LeadBundle\EventListener\ConfigSubscriber::class,
            \MailVotech\MessengerBundle\EventListener\ConfigSubscriber::class,
            \MailVotech\NotificationBundle\EventListener\ConfigSubscriber::class,
            \MailVotech\PageBundle\EventListener\ConfigSubscriber::class,
            \MailVotech\ReportBundle\EventListener\ConfigSubscriber::class,
            \MailVotech\SmsBundle\EventListener\ConfigSubscriber::class,
            \MailVotech\UserBundle\EventListener\ConfigSubscriber::class,
            \MailVotech\WebhookBundle\EventListener\ConfigSubscriber::class,
        ],
        'mailvotech.config_pre_save' => [
            \MailVotechPlugin\MailVotechSocialBundle\EventListener\ConfigSubscriber::class,
            \MailVotech\ApiBundle\EventListener\ConfigSubscriber::class,
            \MailVotech\CampaignBundle\EventListener\ConfigSubscriber::class,
            \MailVotech\CoreBundle\EventListener\ConfigSubscriber::class,
            \MailVotech\EmailBundle\EventListener\ConfigSubscriber::class,
            \MailVotech\PageBundle\EventListener\ConfigSubscriber::class,
            \MailVotech\UserBundle\EventListener\ConfigSubscriber::class,
        ],
        'mailvotech.email_on_send' => [
            \MailVotech\AssetBundle\EventListener\BuilderSubscriber::class,
            \MailVotech\EmailBundle\EventListener\BuilderSubscriber::class,
            DateTimeTokenSubscriber::class,
            \MailVotech\EmailBundle\EventListener\EmailSubscriber::class,
            PointSubscriber::class,
            ProcessUnsubscribeSubscriber::class,
            TokenSubscriber::class,
            \MailVotech\EmailBundle\EventListener\WebhookSubscriber::class,
            \MailVotech\IntegrationsBundle\EventListener\EmailSubscriber::class,
            \MailVotech\LeadBundle\EventListener\EmailSubscriber::class,
            OwnerSubscriber::class,
            \MailVotech\PageBundle\EventListener\BuilderSubscriber::class,
        ],
        'mailvotech.form_on_build' => [
            \MailVotechPlugin\MailVotechSocialBundle\EventListener\FormSubscriber::class,
            \MailVotech\AssetBundle\EventListener\FormSubscriber::class,
            \MailVotech\EmailBundle\EventListener\FormSubscriber::class,
            \MailVotech\FormBundle\EventListener\FormSubscriber::class,
            FormValidationSubscriber::class,
            \MailVotech\LeadBundle\EventListener\FormSubscriber::class,
            \MailVotech\PluginBundle\EventListener\FormSubscriber::class,
        ],
        'mailvotech.lead_post_save' => [
            \MailVotech\IntegrationsBundle\EventListener\LeadSubscriber::class,
            \MailVotech\LeadBundle\EventListener\LeadSubscriber::class,
            \MailVotech\LeadBundle\EventListener\WebhookSubscriber::class,
            \MailVotech\PluginBundle\EventListener\LeadSubscriber::class,
            \MailVotech\PointBundle\EventListener\LeadSubscriber::class,
        ],
    ];

    public function testAllEventSubscribersCanBeCreated(): void
    {
        $this->assertGreaterThanOrEqual(self::MINIMAL_EVENT_SUBSCRIBER_COUNT, count($this->resolveEventSubscribers()));
    }

    public function testEventSubscriberClassesPerEvent(): void
    {
        $eventSubscriberClasses = [];

        foreach ($this->resolveEventSubscribers() as $eventSubscriber) {
            foreach (array_keys($eventSubscriber::getSubscribedEvents()) as $eventName) {
                $eventSubscriberClasses[$eventName][] = $eventSubscriber::class;
            }
        }

        foreach (self::EXPECTED_EVENT_SUBSCRIBER_CLASSES as $eventName => $expectedClasses) {
            $currentClasses = $eventSubscriberClasses[$eventName] ?? [];
            sort($currentClasses);

            $this->assertSame(
                $expectedClasses,
                $currentClasses,
                sprintf('Unexpected local event subscribers for the "%s" event', $eventName)
            );
        }
    }

    /**
     * @return array<int, EventSubscriberInterface>
     */
    private function resolveEventSubscribers(): array
    {
        return array_filter(
            $this->createAllServices(),
            fn (object $service): bool => $service instanceof EventSubscriberInterface && $this->isLocalService($service)
        );
    }
}
