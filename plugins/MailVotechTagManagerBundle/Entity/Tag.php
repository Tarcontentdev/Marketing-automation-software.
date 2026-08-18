<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechTagManagerBundle\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use MailVotech\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use MailVotech\LeadBundle\Entity\Tag as BaseTag;

class Tag extends BaseTag
{
    public static function loadMetadata(ClassMetadata $metadata): void
    {
        $builder = new ClassMetadataBuilder($metadata);
        $builder->setTable('lead_tags')
            ->setEmbeddable()
            ->setCustomRepositoryClass(TagRepository::class);
    }
}
