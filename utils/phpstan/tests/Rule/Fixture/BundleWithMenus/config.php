<?php

declare(strict_types=1);

return [
    'routes'   => [],
    'services' => [
        'menus' => [
            'mailvotech.menu.main' => [
                'alias' => 'main',
            ],
        ],
        'others' => [
            'mailvotech.some.helper' => [
                'class' => 'MailVotech\CoreBundle\Helper\SomeHelper',
            ],
        ],
    ],
];
