<?php

declare(strict_types=1);

namespace MailVotech\ApiBundle\Entity\oAuth2;

use Doctrine\ORM\Mapping as ORM;
use FOS\OAuthServerBundle\Model\RefreshToken as BaseRefreshToken;
use MailVotech\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use MailVotech\UserBundle\Entity\User;

class RefreshToken extends BaseRefreshToken
{
    public static function loadMetadata(ORM\ClassMetadata $metadata): void
    {
        $builder = new ClassMetadataBuilder($metadata);

        $builder->setTable('oauth2_refreshtokens')
            ->addIndex(['token'], 'oauth2_refresh_token_search');

        $builder->createField('id', 'integer')
            ->makePrimaryKey()
            ->generatedValue()
            ->build();

        $builder->createManyToOne('client', 'Client')
            ->addJoinColumn('client_id', 'id', false, false, 'CASCADE')
            ->build();

        $builder->createManyToOne('user', User::class)
            ->addJoinColumn('user_id', 'id', false, false, 'CASCADE')
            ->build();

        $builder->createField('token', 'string')
            ->unique()
            ->build();

        $builder->createField('expiresAt', 'bigint')
            ->columnName('expires_at')
            ->nullable()
            ->build();

        $builder->createField('scope', 'string')
            ->nullable()
            ->build();
    }
}
