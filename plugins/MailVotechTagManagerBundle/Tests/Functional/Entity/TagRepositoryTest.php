<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechTagManagerBundle\Tests\Functional\Entity;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\LeadBundle\Entity\Tag;
use MailVotechPlugin\MailVotechTagManagerBundle\Entity\TagRepository;

final class TagRepositoryTest extends MailVotechMysqlTestCase
{
    private TagRepository $tagRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tagRepository = self::getContainer()->get(TagRepository::class);

        $tags = [
            'tag1',
            'tag2',
            'tag3',
            'tag4',
        ];

        foreach ($tags as $tagName) {
            $tag = new Tag();
            $tag->setTag($tagName);
            $this->tagRepository->saveEntity($tag);
        }
    }

    public function testCountOccurencesReturnsCorrectQuantityOfTags(): void
    {
        $count = $this->tagRepository->countOccurrences('tag2');
        $this->assertSame(1, $count);
    }
}
