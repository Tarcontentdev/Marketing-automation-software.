<?php

declare(strict_types=1);

return [
    'name'        => 'CRM',
    'description' => 'Enables integration with MailVotech supported CRMs.',
    'version'     => '1.0',
    'author'      => 'MailVotech',
    'routes'      => [
        'public' => [
            'mailvotech_integration_contacts' => [
                'path'         => '/plugin/{integration}/contact_data',
                'controller'   => 'MailVotechPlugin\MailVotechCrmBundle\Controller\PublicController::contactDataAction',
                'requirements' => [
                    'integration' => '.+',
                ],
            ],
            'mailvotech_integration_companies' => [
                'path'         => '/plugin/{integration}/company_data',
                'controller'   => 'MailVotechPlugin\MailVotechCrmBundle\Controller\PublicController::companyDataAction',
                'requirements' => [
                    'integration' => '.+',
                ],
            ],
        ],
    ],
];
