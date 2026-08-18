<?php

declare(strict_types=1);

namespace MailVotech\ChannelBundle\Tests\Entity;

use MailVotech\CategoryBundle\Entity\Category;
use MailVotech\ChannelBundle\Entity\Message;
use PHPUnit\Framework\TestCase;

final class MessageTest extends TestCase
{
    public function testMessageUpdatesReflectsInChanges(): void
    {
        $category = new Category();
        $category->setTitle('New Category');
        $category->setAlias('category');
        $category->setBundle('bundle');

        $message = new Message();
        $message->setName('New Message');
        $message->setDescription('random text string for description');
        $message->setCategory($category);
        $message->setPublishDown(new \DateTime());
        $message->setPublishUp(new \DateTime());

        $this->assertIsArray($message->getChanges());
        $this->assertNotEmpty($message->getChanges());
    }
}
