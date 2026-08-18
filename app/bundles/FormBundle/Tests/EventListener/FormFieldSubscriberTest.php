<?php

declare(strict_types=1);

namespace MailVotech\FormBundle\Tests\EventListener;

use MailVotech\FormBundle\EventListener\FormFieldSubscriber;
use MailVotech\FormBundle\FormEvents;
use MailVotech\FormBundle\Model\FieldModel;
use PHPUnit\Framework\TestCase;

final class FormFieldSubscriberTest extends TestCase
{
    private FormFieldSubscriber $subscriber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subscriber = new FormFieldSubscriber($this->createStub(FieldModel::class));
    }

    public function testGetSubscribedEvents(): void
    {
        $this->assertSame(
            [
                FormEvents::FIELD_POST_DELETE => ['onFieldPostDelete', 0],
            ],
            $this->subscriber::getSubscribedEvents()
        );
    }
}
