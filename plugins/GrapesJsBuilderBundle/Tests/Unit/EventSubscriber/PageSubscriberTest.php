<?php

declare(strict_types=1);

namespace MailVotechPlugin\GrapesJsBuilderBundle\Tests\Unit\EventSubscriber;

use MailVotech\PageBundle\Entity\Page;
use MailVotech\PageBundle\Event\PageEvent;
use MailVotechPlugin\GrapesJsBuilderBundle\EventSubscriber\PageSubscriber;
use MailVotechPlugin\GrapesJsBuilderBundle\Integration\Config;
use MailVotechPlugin\GrapesJsBuilderBundle\Model\GrapesJsBuilderModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class PageSubscriberTest extends TestCase
{
    /**
     * @var MockObject&Config
     */
    private MockObject $config;

    /**
     * @var MockObject&GrapesJsBuilderModel
     */
    private MockObject $model;

    private PageSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->config     = $this->createMock(Config::class);
        $this->model      = $this->createMock(GrapesJsBuilderModel::class);
        $this->subscriber = new PageSubscriber($this->config, $this->model);
    }

    public function testOnPagePostSaveSkipsWhenPluginNotPublished(): void
    {
        $this->config->method('isPublished')->willReturn(false);
        $this->model->expects($this->never())->method('addOrEditPageEntity');

        $this->subscriber->onPagePostSave(new PageEvent(new Page()));
    }

    public function testOnPagePostSaveCallsModelWhenPluginPublished(): void
    {
        $page = new Page();

        $this->config->method('isPublished')->willReturn(true);
        $this->model->expects($this->once())
            ->method('addOrEditPageEntity')
            ->with($page);

        $this->subscriber->onPagePostSave(new PageEvent($page));
    }
}
