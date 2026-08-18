<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\Model;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\LeadBundle\Entity\Tag;
use MailVotech\LeadBundle\Entity\TagRepository;
use MailVotech\LeadBundle\Model\TagModel;

final class TagModelFunctionalTest extends MailVotechMysqlTestCase
{
    public function testDeleteOrphanTags(): void
    {
        /** @var TagModel $model */
        $model = self::getContainer()->get(TagModel::class);

        $tags = [
            'tag1',
            'tag2',
            'tag3',
            'tag4',
        ];

        foreach ($tags as $tagName) {
            $tag = new Tag();
            $tag->setTag($tagName);
            $model->saveEntity($tag);
        }

        /** @var TagRepository $tagRepository */
        $tagRepository = $model->getRepository();
        $count         = $tagRepository->count([]);
        $this->assertSame(4, $count);

        $tagRepository->deleteOrphans();
        $count = $tagRepository->count([]);
        $this->assertSame(0, $count);
    }
}
