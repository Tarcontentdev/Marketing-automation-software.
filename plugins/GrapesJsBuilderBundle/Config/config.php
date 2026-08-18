<?php

declare(strict_types=1);

return [
    'name'        => 'GrapesJS Builder',
    'description' => 'GrapesJS Builder with MJML support for MailVotech',
    'version'     => '1.0.0',
    'author'      => 'MailVotech Community',
    'routes'      => [
        'main'   => [
            'grapesjsbuilder_upload' => [
                'path'       => '/grapesjsbuilder/upload',
                'controller' => 'MailVotechPlugin\GrapesJsBuilderBundle\Controller\FileManagerController::uploadAction',
            ],
            'grapesjsbuilder_delete' => [
                'path'       => '/grapesjsbuilder/delete',
                'controller' => 'MailVotechPlugin\GrapesJsBuilderBundle\Controller\FileManagerController::deleteAction',
            ],
            /** @depreacated since MailVotech 5.2, to be removed in 6.0. Use grapesjsbuilder_media instead */
            'grapesjsbuilder_assets' => [
                'path'       => '/grapesjsbuilder/assets',
                'controller' => 'MailVotechPlugin\GrapesJsBuilderBundle\Controller\FileManagerController::assetsAction',
            ],
            'grapesjsbuilder_media' => [
                'path'       => '/grapesjsbuilder/media',
                'controller' => 'MailVotechPlugin\GrapesJsBuilderBundle\Controller\FileManagerController::getMediaAction',
            ],
            'grapesjsbuilder_builder' => [
                'path'       => '/grapesjsbuilder/{objectType}/{objectId}',
                'controller' => 'MailVotechPlugin\GrapesJsBuilderBundle\Controller\GrapesJsController::builderAction',
            ],
            'grapesjsbuilder_editor_state' => [
                'path'       => '/grapesjsbuilder/{objectType}/{objectId}/editor-state',
                'controller' => 'MailVotechPlugin\GrapesJsBuilderBundle\Controller\GrapesJsController::editorStateAction',
                'methods'    => ['GET'],
            ],
        ],
        'public' => [],
        'api'    => [],
    ],
    'menu'        => [],
    'parameters' => [
        'image_path_exclude'     => ['flags', 'mejs'], // exclude certain folders from showing in the image browser
        'static_url'             => '', // optional base url for images
    ],
];
