<?php

namespace MailVotechPlugin\MailVotechClearbitBundle\Controller;

use MailVotech\FormBundle\Controller\FormController;
use MailVotech\LeadBundle\Entity\Company;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Model\CompanyModel;
use MailVotech\LeadBundle\Model\LeadModel;
use MailVotechPlugin\MailVotechClearbitBundle\Form\Type\BatchLookupType;
use MailVotechPlugin\MailVotechClearbitBundle\Form\Type\LookupType;
use MailVotechPlugin\MailVotechClearbitBundle\Helper\LookupHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\Required;

final class ClearbitController extends FormController
{
    private CompanyModel $companyModel;

    private LeadModel $leadModel;

    #[Required]
    public function autowireClearbitController(
        LeadModel $leadModel,
        CompanyModel $companyModel,
    ): void {
        $this->leadModel = $leadModel;
        $this->companyModel = $companyModel;
    }

    /**
     * @param string $objectId
     *
     * @throws \InvalidArgumentException
     */
    public function lookupPersonAction(Request $request, LookupHelper $lookupHelper, $objectId = ''): JsonResponse|Response
    {
        if ('POST' === $request->getMethod()) {
            $data     = $request->request->all()['clearbit_lookup'] ?? [];
            $objectId = $data['objectId'];
        }
        $lead  = $this->leadModel->getEntity($objectId);

        if (!$this->security->hasEntityAccess(
            'lead:leads:editown',
            'lead:leads:editother',
            $lead->getPermissionUser()
        )
        ) {
            $this->addFlashMessage(
                $this->translator->trans('mailvotech.plugin.clearbit.forbidden'),
                [],
                'error'
            );

            return new JsonResponse(
                [
                    'closeModal' => true,
                    'flashes'    => $this->getFlashContent(),
                ]
            );
        }

        if ('GET' === $request->getMethod()) {
            $route = $this->generateUrl(
                'mailvotech_plugin_clearbit_action',
                [
                    'objectAction' => 'lookupPerson',
                ]
            );

            return $this->delegateView(
                [
                    'viewParameters' => [
                        'form' => $this->createForm(
                            LookupType::class,
                            [
                                'objectId' => $objectId,
                            ],
                            [
                                'action' => $route,
                            ]
                        )->createView(),
                        'lookupItem' => $lead->getEmail(),
                    ],
                    'contentTemplate' => '@MailVotechClearbit/Clearbit/lookup.html.twig',
                    'passthroughVars' => [
                        'activeLink'    => '#mailvotech_contact_index',
                        'mailvotechContent' => 'lead',
                        'route'         => $route,
                    ],
                ]
            );
        }
        if ('POST' === $request->getMethod()) {
            try {
                $lookupHelper->lookupContact($lead, array_key_exists('notify', $data));
                $this->addFlashMessage(
                    'mailvotech.lead.batch_leads_affected',
                    [
                        '%count%'     => 1,
                    ]
                );
            } catch (\Exception $ex) {
                $this->addFlashMessage(
                    $ex->getMessage(),
                    [],
                    'error'
                );
            }

            return new JsonResponse(
                [
                    'closeModal' => true,
                    'flashes'    => $this->getFlashContent(),
                ]
            );
        }

        return new Response('Bad Request', 400);
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function batchLookupPersonAction(Request $request, LookupHelper $lookupHelper): JsonResponse|Response
    {
        if ('GET' === $request->getMethod()) {
            $data = $request->query->all()['clearbit_batch_lookup'] ?? [];
        } else {
            $data = $request->request->all()['clearbit_batch_lookup'] ?? [];
        }

        $entities = [];
        if (array_key_exists('ids', $data)) {
            $ids = $data['ids'];

            if (!is_array($ids)) {
                $ids = json_decode($ids, true);
            }

            if (is_array($ids) && count($ids)) {
                $entities = $this->leadModel->getEntities(
                    [
                        'filter' => [
                            'force' => [
                                [
                                    'column' => 'l.id',
                                    'expr'   => 'in',
                                    'value'  => $ids,
                                ],
                            ],
                        ],
                        'ignore_paginator' => true,
                    ]
                );
            }
        }

        $lookupEmails = [];
        if ($count = count($entities)) {
            /** @var Lead $lead */
            foreach ($entities as $lead) {
                if ($this->security->hasEntityAccess(
                    'lead:leads:editown',
                    'lead:leads:editother',
                    $lead->getPermissionUser()
                )
                    && $lead->getEmail()
                ) {
                    $lookupEmails[$lead->getId()] = $lead->getEmail();
                }
            }

            $count = count($lookupEmails);
        }

        if (0 === $count) {
            $this->addFlashMessage(
                $this->translator->trans('mailvotech.plugin.clearbit.empty'),
                [],
                'error'
            );

            return new JsonResponse(
                [
                    'closeModal' => true,
                    'flashes'    => $this->getFlashContent(),
                ]
            );
        }
        if ($count > 20) {
            $this->addFlashMessage(
                $this->translator->trans('mailvotech.plugin.clearbit.toomany'),
                [],
                'error'
            );

            return new JsonResponse(
                [
                    'closeModal' => true,
                    'flashes'    => $this->getFlashContent(),
                ]
            );
        }

        if ('GET' === $request->getMethod()) {
            $route = $this->generateUrl(
                'mailvotech_plugin_clearbit_action',
                [
                    'objectAction' => 'batchLookupPerson',
                ]
            );

            return $this->delegateView(
                [
                    'viewParameters' => [
                        'form' => $this->createForm(
                            BatchLookupType::class,
                            [],
                            [
                                'action' => $route,
                            ]
                        )->createView(),
                        'lookupItems' => array_values($lookupEmails),
                    ],
                    'contentTemplate' => '@MailVotechClearbit/Clearbit/batchLookup.html.twig',
                    'passthroughVars' => [
                        'activeLink'    => '#mailvotech_contact_index',
                        'mailvotechContent' => 'leadBatch',
                        'route'         => $route,
                    ],
                ]
            );
        }
        if ('POST' === $request->getMethod()) {
            $notify = array_key_exists('notify', $data);
            foreach ($lookupEmails as $id => $lookupEmail) {
                if ($lead = $this->leadModel->getEntity($id)) {
                    try {
                        $lookupHelper->lookupContact($lead, $notify);
                    } catch (\Exception $ex) {
                        $this->addFlashMessage(
                            $ex->getMessage(),
                            [],
                            'error'
                        );
                        --$count;
                    }
                }
            }

            if ($count) {
                $this->addFlashMessage(
                    'mailvotech.lead.batch_leads_affected',
                    [
                        '%count%'     => $count,
                    ]
                );
            }

            return new JsonResponse(
                [
                    'closeModal' => true,
                    'flashes'    => $this->getFlashContent(),
                ]
            );
        }

        return new Response('Bad Request', 400);
    }

    /* COMPANY */

    /**
     * @param string $objectId
     *
     * @throws \InvalidArgumentException
     */
    public function lookupCompanyAction(Request $request, LookupHelper $lookupHelper, $objectId = ''): JsonResponse|Response
    {
        if ('POST' === $request->getMethod()) {
            $data     = $request->request->all()['clearbit_lookup'] ?? [];
            $objectId = $data['objectId'];
        }
        /** @var Company $company */
        $company = $this->companyModel->getEntity($objectId);

        if ('GET' === $request->getMethod()) {
            $route = $this->generateUrl(
                'mailvotech_plugin_clearbit_action',
                [
                    'objectAction' => 'lookupCompany',
                ]
            );

            $website = $company->getFieldValue('companywebsite');

            if (!$website) {
                $this->addFlashMessage(
                    $this->translator->trans('mailvotech.plugin.clearbit.compempty'),
                    [],
                    'error'
                );

                return new JsonResponse(
                    [
                        'closeModal' => true,
                        'flashes'    => $this->getFlashContent(),
                    ]
                );
            }
            $parse = parse_url($website);

            return $this->delegateView(
                [
                    'viewParameters' => [
                        'form' => $this->createForm(
                            LookupType::class,
                            [
                                'objectId' => $objectId,
                            ],
                            [
                                'action' => $route,
                            ]
                        )->createView(),
                        'lookupItem' => $parse['host'],
                    ],
                    'contentTemplate' => '@MailVotechClearbit/Clearbit/lookup.html.twig',
                    'passthroughVars' => [
                        'activeLink'    => '#mailvotech_company_index',
                        'mailvotechContent' => 'company',
                        'route'         => $route,
                    ],
                ]
            );
        }
        if ('POST' === $request->getMethod()) {
            try {
                $lookupHelper->lookupCompany($company, array_key_exists('notify', $data));
                $this->addFlashMessage(
                    'mailvotech.company.batch_companies_affected',
                    [
                        '%count%'     => 1,
                    ]
                );
            } catch (\Exception $ex) {
                $this->addFlashMessage(
                    $ex->getMessage(),
                    [],
                    'error'
                );
            }

            return new JsonResponse(
                [
                    'closeModal' => true,
                    'flashes'    => $this->getFlashContent(),
                ]
            );
        }

        return new Response('Bad Request', 400);
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function batchLookupCompanyAction(Request $request, LookupHelper $lookupHelper): JsonResponse|Response
    {
        if ('GET' === $request->getMethod()) {
            $data = $request->query->all()['clearbit_batch_lookup'] ?? [];
        } else {
            $data = $request->request->all()['clearbit_batch_lookup'] ?? [];
        }

        $entities = [];
        if (array_key_exists('ids', $data)) {
            $ids = $data['ids'];

            if (!is_array($ids)) {
                $ids = json_decode($ids, true);
            }

            if (is_array($ids) && count($ids)) {
                $entities = $this->companyModel->getEntities(
                    [
                        'filter' => [
                            'force' => [
                                [
                                    'column' => 'comp.id',
                                    'expr'   => 'in',
                                    'value'  => $ids,
                                ],
                            ],
                        ],
                        'ignore_paginator' => true,
                    ]
                );
            }
        }

        $lookupWebsites = [];
        if ($count = count($entities)) {
            /** @var Company $company */
            foreach ($entities as $company) {
                if ($company->getFieldValue('companywebsite')) {
                    $website = $company->getFieldValue('companywebsite');
                    $parse   = parse_url($website);
                    if (!isset($parse['host'])) {
                        continue;
                    }
                    $lookupWebsites[$company->getId()] = $parse['host'];
                }
            }

            $count = count($lookupWebsites);
        }

        if (0 === $count) {
            $this->addFlashMessage(
                $this->translator->trans('mailvotech.plugin.clearbit.compempty'),
                [],
                'error'
            );

            return new JsonResponse(
                [
                    'closeModal' => true,
                    'flashes'    => $this->getFlashContent(),
                ]
            );
        }
        if ($count > 20) {
            $this->addFlashMessage(
                $this->translator->trans('mailvotech.plugin.clearbit.comptoomany'),
                [],
                'error'
            );

            return new JsonResponse(
                [
                    'closeModal' => true,
                    'flashes'    => $this->getFlashContent(),
                ]
            );
        }

        if ('GET' === $request->getMethod()) {
            $route = $this->generateUrl(
                'mailvotech_plugin_clearbit_action',
                [
                    'objectAction' => 'batchLookupCompany',
                ]
            );

            return $this->delegateView(
                [
                    'viewParameters' => [
                        'form' => $this->createForm(
                            BatchLookupType::class,
                            [],
                            [
                                'action' => $route,
                            ]
                        )->createView(),
                        'lookupItems' => array_values($lookupWebsites),
                    ],
                    'contentTemplate' => '@MailVotechClearbit/Clearbit/batchLookup.html.twig',
                    'passthroughVars' => [
                        'activeLink'    => '#mailvotech_company_index',
                        'mailvotechContent' => 'companyBatch',
                        'route'         => $route,
                    ],
                ]
            );
        }
        if ('POST' === $request->getMethod()) {
            $notify = array_key_exists('notify', $data);
            foreach ($lookupWebsites as $id => $lookupWebsite) {
                if ($company = $this->companyModel->getEntity($id)) {
                    try {
                        $lookupHelper->lookupCompany($company, $notify);
                    } catch (\Exception $ex) {
                        $this->addFlashMessage(
                            $ex->getMessage(),
                            [],
                            'error'
                        );
                        --$count;
                    }
                }
            }

            if ($count) {
                $this->addFlashMessage(
                    'mailvotech.company.batch_companies_affected',
                    [
                        '%count%'     => $count,
                    ]
                );
            }

            return new JsonResponse(
                [
                    'closeModal' => true,
                    'flashes'    => $this->getFlashContent(),
                ]
            );
        }

        return new Response('Bad Request', 400);
    }
}
