<?php

namespace MailVotech\LeadBundle\Controller;

use MailVotech\CoreBundle\Controller\AbstractFormController;
use MailVotech\LeadBundle\Form\Type\BatchType;
use MailVotech\LeadBundle\Model\ListModel;
use MailVotech\LeadBundle\Model\SegmentActionModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\Required;

final class BatchSegmentController extends AbstractFormController
{
    private SegmentActionModel $segmentActionModel;

    private ListModel $segmentModel;

    #[Required]
    public function autowireBatchSegmentController(
        SegmentActionModel $segmentActionModel,
        ListModel $segmentModel,
    ): void {
        $this->segmentActionModel = $segmentActionModel;
        $this->segmentModel       = $segmentModel;
    }

    /**
     * API for batch action.
     */
    public function setAction(Request $request): JsonResponse
    {
        $params     = $request->query->all()['lead_batch'] ?? $request->request->all()['lead_batch'] ?? [];
        $contactIds = empty($params['ids']) ? [] : json_decode($params['ids']);

        if ($contactIds && is_array($contactIds)) {
            $segmentsToAdd    = $params['add'] ?? [];
            $segmentsToRemove = $params['remove'] ?? [];

            if ($segmentsToAdd) {
                $this->segmentActionModel->addContacts($contactIds, $segmentsToAdd);
            }

            if ($segmentsToRemove) {
                $this->segmentActionModel->removeContacts($contactIds, $segmentsToRemove);
            }

            $this->addFlashMessage('mailvotech.lead.batch_leads_affected', [
                '%count%' => count($contactIds),
            ]);
        } else {
            $this->addFlashMessage('mailvotech.core.error.ids.missing');
        }

        return new JsonResponse([
            'closeModal' => true,
            'flashes'    => $this->getFlashContent(),
        ]);
    }

    /**
     * View for batch action.
     */
    public function indexAction(): Response
    {
        $route = $this->generateUrl('mailvotech_segment_batch_contact_set');
        $lists = $this->segmentModel->getUserLists();
        $items = [];

        foreach ($lists as $list) {
            $items[$list['name'].' ('.$list['id'].')'] = $list['id'];
        }

        return $this->delegateView(
            [
                'viewParameters' => [
                    'form' => $this->createForm(
                        BatchType::class,
                        [],
                        [
                            'items'  => $items,
                            'action' => $route,
                        ]
                    )->createView(),
                ],
                'contentTemplate' => '@MailVotechLead/Batch/form.html.twig',
                'passthroughVars' => [
                    'activeLink'    => '#mailvotech_contact_index',
                    'mailvotechContent' => 'leadBatch',
                    'route'         => $route,
                ],
            ]
        );
    }
}
