<?php

declare(strict_types=1);

return [
    'routes' => [
        'main' => [
            'mailvotech_asset_index' => [
                'path'       => '/assets/{page}',
                'controller' => 'MailVotech\AssetBundle\Controller\AssetController::indexAction',
            ],
            'mailvotech_asset_remote' => [
                'path'       => '/assets/remote',
                'controller' => 'MailVotech\AssetBundle\Controller\AssetController::remoteAction',
            ],
            'mailvotech_asset_action' => [
                'path'       => '/assets/{objectAction}/{objectId}',
                'controller' => 'MailVotech\AssetBundle\Controller\AssetController::executeAction',
            ],
        ],
        'api' => [
            'mailvotech_api_assetsstandard' => [
                'standard_entity' => true,
                'name'            => 'assets',
                'path'            => '/assets',
                'controller'      => MailVotech\AssetBundle\Controller\Api\AssetApiController::class,
            ],
        ],
        'public' => [
            'mailvotech_asset_download' => [
                'path'       => '/asset/{slug}',
                'controller' => 'MailVotech\AssetBundle\Controller\PublicController::downloadAction',
                'defaults'   => [
                    'slug' => '',
                ],
            ],
        ],
    ],

    'menu' => [
        'main' => [
            'items' => [
                'mailvotech.asset.assets' => [
                    'route'    => 'mailvotech_asset_index',
                    'access'   => ['asset:assets:viewown', 'asset:assets:viewother'],
                    'parent'   => 'mailvotech.core.components',
                    'priority' => 300,
                ],
            ],
        ],
    ],

    'categories' => [
        'asset' => [
            'class' => MailVotech\AssetBundle\Entity\Asset::class,
        ],
    ],

    'parameters' => [
        'upload_dir'          => '%mailvotech.application_dir%/media/files',
        'max_size'            => '6',
        'allowed_extensions'  => ['csv', 'doc', 'docx', 'epub', 'gif', 'jpg', 'jpeg', 'mpg', 'mpeg', 'mp3', 'odt', 'odp', 'ods', 'pdf', 'png', 'ppt', 'pptx', 'tif', 'tiff', 'txt', 'xls', 'xlsx', 'wav'],
        'streamed_extensions' => ['gif', 'jpg', 'jpeg', 'mpg', 'mpeg', 'mp3', 'pdf', 'png', 'wav'],
    ],
];
