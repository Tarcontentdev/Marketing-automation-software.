<?php

declare(strict_types=1);

return [
    'routes'   => [
        'main' => [
            'mailvotech_core_ajax' => [
                'path'       => '/ajax',
                'controller' => 'MailVotech\CoreBundle\Controller\AjaxController::delegateAjaxAction',
            ],
        ],
    ],
    'menu'     => [
        'main' => [
            'mailvotech.core.components' => [
                'id'        => 'mailvotech_components_root',
                'iconClass' => 'ri-puzzle-2-line',
                'priority'  => 60,
            ],
        ],
    ],
    'services' => [
        'helpers'  => [
            'mailvotech.helper.bundle' => [
                'class'     => MailVotech\CoreBundle\Helper\BundleHelper::class,
                'arguments' => [
                    '%mailvotech.bundles%',
                    '%mailvotech.plugin.bundles%',
                ],
            ],
        ],
        'other'    => [
            'mailvotech.http.client' => [
                'class' => GuzzleHttp\Client::class,
            ],
        ],
        'fixtures' => [
            'mailvotech.test.fixture' => [
                'class'    => 'Foo\Bar\NonExisting',
                'optional' => true,
            ],
        ],
    ],

    'ip_lookup_services' => [
        'extreme-ip' => [
            'display_name' => 'Extreme-IP',
            'class'        => MailVotech\CoreBundle\IpLookup\ExtremeIpLookup::class,
        ],
    ],

    'parameters' => [
        'log_path'      => '%kernel.project_dir%/var/logs',
        'max_log_files' => 7,
        'image_path'    => 'media/images',
        'bool_value'    => false,
        'null_value'    => null,
        'array_value'   => [],
    ],
];
