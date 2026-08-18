<?php

declare(strict_types=1);

namespace MailVotech\StageBundle\Tests\Functional\EventListener;

use MailVotech\CoreBundle\Entity\IpAddress;
use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Event\LeadMergeEvent;
use MailVotech\StageBundle\Entity\LeadStageLog;
use MailVotech\StageBundle\Entity\Stage;
use MailVotech\StageBundle\EventListener\LeadSubscriber;
use MailVotech\StageBundle\Model\StageModel;

final class LeadSubscriberTest extends MailVotechMysqlTestCase
{
    private StageModel $model;

    protected function setUp(): void
    {
        parent::setUp();

        $this->model = self::getContainer()->get(StageModel::class);
    }

    public function testUpdateLead(): void
    {
        $this->assertEmpty($this->em->getRepository(LeadStageLog::class)->findAll());
        $ipAddress = new IpAddress();
        $ipAddress->setIpAddress('13.13.13.13');
        $this->em->persist($ipAddress);

        $leadOne    = $this->createLead('one@example.com');
        $leadTwo    = $this->createLead('two@example.com');
        $stageOne   = $this->createStage('one', 10);
        $stageTwo   = $this->createStage('two', 50);
        $stageThree = $this->createStage('three', 100);

        $this->createLeadStageLog($leadOne, $stageOne, $ipAddress);
        $this->createLeadStageLog($leadTwo, $stageOne, $ipAddress);

        $this->createLeadStageLog($leadOne, $stageTwo, $ipAddress);
        $this->createLeadStageLog($leadTwo, $stageTwo, $ipAddress);

        $this->createLeadStageLog($leadTwo, $stageThree, $ipAddress);

        $this->assertCount(5, $this->em->getRepository(LeadStageLog::class)->findAll());

        $leadMergeEvent = new LeadMergeEvent($leadTwo, $leadOne);

        /** @var LeadSubscriber $subscriber */
        $subscriber = self::getContainer()->get(LeadSubscriber::class);

        $subscriber->onLeadMerge($leadMergeEvent);

        $this->assertCount(3, $this->em->getRepository(LeadStageLog::class)->findAll());
    }

    private function createLead(string $email): Lead
    {
        $lead = new Lead();
        $lead->setEmail($email);
        $lead->setPoints(10);

        $this->model->getRepository()->saveEntity($lead);

        return $lead;
    }

    private function createStage(string $name, int $weight): Stage
    {
        $stage = new Stage();
        $stage->setName($name);
        $stage->setWeight($weight);

        $this->model->getRepository()->saveEntity($stage);

        return $stage;
    }

    private function createLeadStageLog(Lead $lead, Stage $stage, IpAddress $ipAddress): void
    {
        $log = new LeadStageLog();
        $log->setLead($lead);
        $log->setIpAddress($ipAddress);
        $log->setStage($stage);
        $log->setDateFired(new \DateTime());

        $this->model->getRepository()->saveEntity($log);
    }
}
