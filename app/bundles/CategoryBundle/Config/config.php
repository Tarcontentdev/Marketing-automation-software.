<?php

declare(strict_types=1);

return [
    'routes' => [
        'main' => [
            'mailvotech_category_batch_contact_set' => [
                'path'       => '/categories/batch/contact/set',
                'controller' => 'MailVotech\CategoryBundle\Controller\BatchContactController::execAction',
            ],
            'mailvotech_category_batch_contact_view' => [
                'path'       => '/categories/batch/contact/view',
                'controller' => 'MailVotech\CategoryBundle\Controller\BatchContactController::indexAction',
            ],
            'mailvotech_category_index' => [
                'path'       => '/categories/{bundle}/{page}',
                'controller' => 'MailVotech\CategoryBundle\Controller\CategoryController::indexAction',
                'defaults'   => [
                    'bundle' => 'category',
                ],
            ],
            'mailvotech_category_action' => [
                'path'       => '/categories/{bundle}/{objectAction}/{objectId}',
                'controller' => 'MailVotech\CategoryBundle\Controller\CategoryController::executeCategoryAction',
                'defaults'   => [
                    'bundle' => 'category',
                ],
            ],
        ],
        'api' => [
            'mailvotech_api_categoriesstandard' => [
                'standard_entity' => true,
                'name'            => 'categories',
                'path'            => '/categories',
                'controller'      => MailVotech\CategoryBundle\Controller\Api\CategoryApiController::class,
            ],
        ],
    ],

    'menu' => [
        'admin' => [
            'mailvotech.category.menu.index' => [
                'route'     => 'mailvotech_category_index',
                'access'    => 'category:categories:view',
                'iconClass' => 'ri-folder-6-line',
                'id'        => 'mailvotech_category_index',
                'parent'    => 'mailvotech.core.general',
                'priority'  => 20,
            ],
        ],
    ],
];
