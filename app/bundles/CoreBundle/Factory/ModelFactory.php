<?php

namespace MailVotech\CoreBundle\Factory;

use MailVotech\CoreBundle\Model\MailVotechModelInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * @template M of object
 */
class ModelFactory
{
    public function __construct(
        private readonly ServiceLocator $container,
    ) {
    }

    /**
     * @return ($modelNameKey is 'asset' ? \MailVotech\AssetBundle\Model\AssetModel
     *  : ($modelNameKey is 'campaign' ? \MailVotech\CampaignBundle\Model\CampaignModel
     *  : ($modelNameKey is 'campaign.event' ? \MailVotech\CampaignBundle\Model\EventModel
     *  : ($modelNameKey is 'campaign.event_log' ? \MailVotech\CampaignBundle\Model\EventLogModel
     *  : ($modelNameKey is 'category' ? \MailVotech\CategoryBundle\Model\CategoryModel
     *  : ($modelNameKey is 'channel.message' ? \MailVotech\ChannelBundle\Model\MessageModel
     *  : ($modelNameKey is 'channel.queue' ? \MailVotech\ChannelBundle\Model\MessageQueueModel
     *  : ($modelNameKey is 'core.auditlog' ? \MailVotech\CoreBundle\Model\AuditLogModel
     *  : ($modelNameKey is 'core.notification' ? \MailVotech\CoreBundle\Model\NotificationModel
     *  : ($modelNameKey is 'dashboard' ? \MailVotech\DashboardBundle\Model\DashboardModel
     *  : ($modelNameKey is 'dynamicContent' ? \MailVotech\DynamicContentBundle\Model\DynamicContentModel
     *  : ($modelNameKey is 'email' ? \MailVotech\EmailBundle\Model\EmailModel
     *  : ($modelNameKey is 'focus' ? \MailVotechPlugin\MailVotechFocusBundle\Model\FocusModel
     *  : ($modelNameKey is 'form' ? \MailVotech\FormBundle\Model\FormModel
     *  : ($modelNameKey is 'form.action' ? \MailVotech\FormBundle\Model\ActionModel
     *  : ($modelNameKey is 'form.field' ? \MailVotech\FormBundle\Model\FieldModel
     *  : ($modelNameKey is 'form.form' ? \MailVotech\FormBundle\Model\FormModel
     *  : ($modelNameKey is 'form.submission' ? \MailVotech\FormBundle\Model\SubmissionModel
     *  : ($modelNameKey is 'form.submission_result_loader' ? \MailVotech\FormBundle\Model\SubmissionResultLoader
     *  : ($modelNameKey is 'lead' ? \MailVotech\LeadBundle\Model\LeadModel
     *  : ($modelNameKey is 'lead.company' ? \MailVotech\LeadBundle\Model\CompanyModel
     *  : ($modelNameKey is 'lead.device' ? \MailVotech\LeadBundle\Model\DeviceModel
     *  : ($modelNameKey is 'lead.export_scheduler' ? \MailVotech\LeadBundle\Model\ContactExportSchedulerModel
     *  : ($modelNameKey is 'lead.field' ? \MailVotech\LeadBundle\Model\FieldModel
     *  : ($modelNameKey is 'lead.lead' ? \MailVotech\LeadBundle\Model\LeadModel
     *  : ($modelNameKey is 'lead.list' ? \MailVotech\LeadBundle\Model\ListModel
     *  : ($modelNameKey is 'lead.note' ? \MailVotech\LeadBundle\Model\NoteModel
     *  : ($modelNameKey is 'lead.tag' ? \MailVotech\LeadBundle\Model\TagModel
     *  : ($modelNameKey is 'notification' ? \MailVotech\CoreBundle\Model\NotificationModel
     *  : ($modelNameKey is 'page' ? \MailVotech\PageBundle\Model\PageModel
     *  : ($modelNameKey is 'page.page' ? \MailVotech\PageBundle\Model\PageModel
     *  : ($modelNameKey is 'page.trackable' ? \MailVotech\PageBundle\Model\TrackableModel
     *  : ($modelNameKey is 'plugin' ? \MailVotech\PluginBundle\Model\PluginModel
     *  : ($modelNameKey is 'point' ? \MailVotech\PointBundle\Model\PointModel
     *  : ($modelNameKey is 'point.insight' ? \MailVotech\PointBundle\Model\InsightModel
     *  : ($modelNameKey is 'point.trigger' ? \MailVotech\PointBundle\Model\TriggerModel
     *  : ($modelNameKey is 'point.triggerevent' ? \MailVotech\PointBundle\Model\TriggerEventModel
     *  : ($modelNameKey is 'report' ? \MailVotech\ReportBundle\Model\ReportModel
     *  : ($modelNameKey is 'sms' ? \MailVotech\SmsBundle\Model\SmsModel
     *  : ($modelNameKey is 'social.monitoring' ? \MailVotechPlugin\MailVotechSocialBundle\Model\MonitoringModel
     *  : ($modelNameKey is 'social.postcount' ? \MailVotechPlugin\MailVotechSocialBundle\Model\PostCountModel
     *  : ($modelNameKey is 'social.tweet' ? \MailVotechPlugin\MailVotechSocialBundle\Model\TweetModel
     *  : ($modelNameKey is 'stage' ? \MailVotech\StageBundle\Model\StageModel
     *  : ($modelNameKey is 'stage.stage' ? \MailVotech\StageBundle\Model\StageModel
     *  : ($modelNameKey is 'tagmanager.tag' ? \MailVotechPlugin\MailVotechTagManagerBundle\Model\TagModel
     *  : ($modelNameKey is 'user' ? \MailVotech\UserBundle\Model\UserModel
     *  : ($modelNameKey is 'user.role' ? \MailVotech\UserBundle\Model\RoleModel
     *  : ($modelNameKey is 'user.user' ? \MailVotech\UserBundle\Model\UserModel
     *  : ($modelNameKey is 'webhook' ? \MailVotech\WebhookBundle\Model\WebhookModel
     *      : \MailVotech\CoreBundle\Model\AbstractCommonModel<object>)))))))))))))))))))))))))))))))))))))))))))))))))
     */
    public function getModel(string $modelNameKey): MailVotechModelInterface
    {
        if (class_exists($modelNameKey) && $this->container->has($modelNameKey)) {
            return $this->container->get($modelNameKey);
        }

        // Shortcut for models with the same name as the bundle
        if (!str_contains($modelNameKey, '.')) {
            $modelNameKey = "{$modelNameKey}.{$modelNameKey}";
        }

        $parts = explode('.', $modelNameKey);

        if (2 !== count($parts)) {
            throw new \InvalidArgumentException($modelNameKey.' is not a valid model key.');
        }

        [$bundle, $name] = $parts;

        // The container is now case sensitive
        $containerKey = sprintf('mailvotech.%s.model.%s', $bundle, $name);

        if ($this->container->has($containerKey)) {
            return $this->container->get($containerKey);
        }

        throw new \InvalidArgumentException($containerKey.' is not a registered model container key.');
    }

    public function hasModel(string $modelNameKey): bool
    {
        try {
            $this->getModel($modelNameKey);

            return true;
        } catch (\InvalidArgumentException) {
            return false;
        }
    }
}
