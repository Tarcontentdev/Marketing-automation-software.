<?php

declare(strict_types=1);

namespace MailVotech\CampaignBundle\Tests\Helper;

use MailVotech\CampaignBundle\Entity\Campaign;
use MailVotech\CampaignBundle\Event\CampaignLeadChangeEvent;
use MailVotech\CampaignBundle\Helper\CampaignEventHelper;
use MailVotech\CampaignBundle\Tests\CampaignTestAbstract;

final class CampaignEventHelperTest extends CampaignTestAbstract
{
    public function testValidateLeadChangeTriggerWithEmptyCampaigns(): void
    {
        $eventDetails = new CampaignLeadChangeEvent(new Campaign(), [], 'badaction');
        $event        = [
            'properties' => [
                'campaigns' => [],
                'action'    => 'added',
            ],
            'campaign' => [
                'id' => null,
            ],
        ];
        $result = CampaignEventHelper::validateLeadChangeTrigger($eventDetails, $event);
        $this->assertFalse($result);
    }

    public function testValidateLeadChangeTriggerWithUnmatchingCampaignsAndInvalidAction(): void
    {
        $eventDetails = new CampaignLeadChangeEvent(new Campaign(), [], 'badaction');
        $event        = [
            'properties' => [
                'campaigns' => [3],
                'action'    => 'added',
            ],
            'campaign' => [
                'id' => 4,
            ],
        ];
        $result = CampaignEventHelper::validateLeadChangeTrigger($eventDetails, $event);
        $this->assertFalse($result);
    }

    public function testValidateLeadChangeTriggerWithMatchingCampaignsAndInvalidAction(): void
    {
        $eventDetails = new CampaignLeadChangeEvent(new Campaign(), [], 'removed');
        $event        = [
            'properties' => [
                'campaigns' => [3],
                'action'    => 'added',
            ],
            'campaign' => [
                'id' => 3,
            ],
        ];
        $result = CampaignEventHelper::validateLeadChangeTrigger($eventDetails, $event);
        $this->assertFalse($result);
    }

    public function testValidateLeadChangeTriggerWithMatchingCampaignsAndVariousActions(): void
    {
        $actions = [
            'added'   => true,
            'removed' => true,
            'invalid' => false,
        ];

        foreach ($actions as $action => $expectedResult) {
            $campaignId   = 3;
            $eventDetails = new CampaignLeadChangeEvent(new Campaign(), [], $action);
            $event        = [
                'properties' => [
                    'campaigns' => [$campaignId, 8],
                    'action'    => $action,
                ],
                'campaign' => [
                    'id' => $campaignId,
                ],
            ];
            $result = CampaignEventHelper::validateLeadChangeTrigger($eventDetails, $event);
            $this->assertSame($expectedResult, $result);
        }
    }
}
