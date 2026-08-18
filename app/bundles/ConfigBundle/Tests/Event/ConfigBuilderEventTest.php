<?php

declare(strict_types=1);

namespace MailVotech\ConfigBundle\Tests\Event;

use MailVotech\ConfigBundle\Event\ConfigBuilderEvent;
use MailVotech\CoreBundle\Tests\CommonMocks;

final class ConfigBuilderEventTest extends CommonMocks
{
    public function testAddForm(): void
    {
        $event = $this->initEvent();
        $form  = ['formAlias' => 'testform'];
        $event->addForm($form);

        $forms = $event->getForms();

        $this->assertEquals($form, $forms[$form['formAlias']]);
    }

    public function testRemoveForm(): void
    {
        $event = $this->initEvent();
        $form  = ['formAlias' => 'testform'];

        $event->addForm($form);

        $result = $event->removeForm($form['formAlias']);
        $forms  = $event->getForms();

        $this->assertSame([], $forms);
        $this->assertTrue($result);
    }

    protected function initEvent(): ConfigBuilderEvent
    {
        return new ConfigBuilderEvent($this->getBundleHelperMock());
    }
}
