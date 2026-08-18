<?php

namespace MailVotech\NotificationBundle\Api;

use GuzzleHttp\Client;
use MailVotech\NotificationBundle\Entity\Notification;
use MailVotech\PageBundle\Model\TrackableModel;
use MailVotech\PluginBundle\Helper\IntegrationHelper;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractNotificationApi
{
    public function __construct(
        protected Client $http,
        protected TrackableModel $trackableModel,
        protected IntegrationHelper $integrationHelper,
    ) {
    }

    /**
     * @param string $endpoint One of "apps", "players", or "notifications"
     * @param array  $data     Array of data to send
     */
    abstract public function send(string $endpoint, array $data): ResponseInterface;

    /**
     * @return ResponseInterface
     */
    abstract public function sendNotification($id, Notification $notification);

    /**
     * Convert a non-tracked url to a tracked url.
     *
     * @param string $url
     *
     * @return string
     */
    public function convertToTrackedUrl($url, array $clickthrough, Notification $notification)
    {
        $trackable = $this->trackableModel->getTrackableByUrl($url, 'notification', $clickthrough['notification']);

        return $this->trackableModel->generateTrackableUrl($trackable, $clickthrough, [], $notification->getUtmTags());
    }
}
