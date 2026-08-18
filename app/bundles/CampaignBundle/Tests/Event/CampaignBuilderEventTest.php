<?php

declare(strict_types=1);

namespace MailVotech\CampaignBundle\Tests\Event;

use MailVotech\AssetBundle\Form\Type\PointActionAssetDownloadType;
use MailVotech\AssetBundle\Helper\PointActionHelper;
use MailVotech\CampaignBundle\Event\CampaignBuilderEvent;
use MailVotech\CampaignBundle\Tests\CampaignTestAbstract;
use MailVotech\CoreBundle\Translation\Translator;
use MailVotech\FormBundle\Form\Type\CampaignEventFormFieldValueType;

final class CampaignBuilderEventTest extends CampaignTestAbstract
{
    public function testAddGetDecision(): void
    {
        $decisionKey = 'email.open';
        $decision    = [
            'label'                  => 'mailvotech.email.campaign.event.open',
            'description'            => 'mailvotech.email.campaign.event.open_descr',
            'eventName'              => 'mailvotech.email.on_campaign_trigger_decision',
            'connectionRestrictions' => [
                'source' => [
                    'action' => [
                        'email.send',
                    ],
                ],
            ],
        ];
        $event = $this->initEvent();
        $event->addDecision(
            $decisionKey,
            $decision
        );

        $decisions = $event->getDecisions();
        $this->assertSame([$decisionKey => $decision], $decisions);
    }

    public function testEventDecisionSort(): void
    {
        $decision = [
            'label'                  => 'mailvotech.email.campaign.event.open',
            'description'            => 'mailvotech.email.campaign.event.open_descr',
            'eventName'              => 'mailvotech.email.on_campaign_trigger_decision',
            'connectionRestrictions' => [
                'source' => [
                    'action' => [
                        'email.send',
                    ],
                ],
            ],
        ];
        $event = $this->initEvent();

        // add 3 unsorted decisions
        $event->addDecision('email.open1', $decision);
        $decision['label'] = 'mailvotech.email.campaign.event.open.3';
        $event->addDecision('email.open3', $decision);
        $decision['label'] = 'mailvotech.email.campaign.event.open.2';
        $event->addDecision('email.open2', $decision);

        $decisions = $event->getDecisions();

        $this->assertCount(3, $decisions);

        $shouldBe = 1;
        foreach ($decisions as $key => $resultDecision) {
            $this->assertSame('email.open'.$shouldBe, $key);
            ++$shouldBe;
        }
    }

    public function testEventConditionSort(): void
    {
        $condition = [
            'label'       => 'mailvotech.form.campaign.event.field_value',
            'description' => 'mailvotech.form.campaign.event.field_value_descr',
            'formType'    => CampaignEventFormFieldValueType::class,
            'formTheme'   => '@MailVotechForm/FormTheme/FieldValueCondition/_campaignevent_form_field_value_widget.html.twig',
            'eventName'   => 'mailvotech.form.on_campaign_trigger_condition',
        ];
        $event = $this->initEvent();

        // add 3 unsorted conditions
        $event->addCondition('form.field_value1', $condition);
        $condition['label'] = 'mailvotech.form.campaign.event.field_value.3';
        $event->addCondition('form.field_value3', $condition);
        $condition['label'] = 'mailvotech.form.campaign.event.field_value.2';
        $event->addCondition('form.field_value2', $condition);

        $conditions = $event->getConditions();

        $this->assertCount(3, $conditions);

        $shouldBe = 1;
        foreach ($conditions as $key => $resultCondition) {
            $this->assertSame('form.field_value'.$shouldBe, $key);
            ++$shouldBe;
        }
    }

    public function testEventActionSort(): void
    {
        $action = [
            'group'       => 'mailvotech.asset.actions',
            'label'       => 'mailvotech.asset.point.action.download',
            'description' => 'mailvotech.asset.point.action.download_descr',
            'callback'    => [PointActionHelper::class, 'validateAssetDownload'],
            'formType'    => PointActionAssetDownloadType::class,
        ];
        $event = $this->initEvent();

        // add 3 unsorted actions
        $event->addAction('asset.download1', $action);
        $action['label'] = 'mailvotech.asset.point.action.download.3';
        $event->addAction('asset.download3', $action);
        $action['label'] = 'mailvotech.asset.point.action.download.2';
        $event->addAction('asset.download2', $action);

        $actions = $event->getActions();

        $this->assertCount(3, $actions);

        $shouldBe = 1;
        foreach ($actions as $key => $resultAction) {
            $this->assertSame('asset.download'.$shouldBe, $key);
            ++$shouldBe;
        }
    }

    protected function initEvent(): CampaignBuilderEvent
    {
        $translator = $this->createMock(Translator::class);

        $translator
            ->method('trans')
            ->willReturnCallback(function (): string {
                $args = func_get_args();

                return $args[0];
            });

        return new CampaignBuilderEvent($translator);
    }
}
