<?php

namespace MailVotech\CategoryBundle\Controller;

use MailVotech\CategoryBundle\Model\CategoryModel;
use MailVotech\CategoryBundle\Model\ContactActionModel;
use MailVotech\CoreBundle\Controller\AbstractFormController;
use MailVotech\LeadBundle\Form\Type\BatchType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\Required;

final class BatchContactController extends AbstractFormController
{
    private ContactActionModel $actionModel;

    private CategoryModel $categoryModel;

    #[Required]
    public function autowireBatchContactController(
        ContactActionModel $actionModel,
        CategoryModel $categoryModel,
    ): void {
        $this->actionModel   = $actionModel;
        $this->categoryModel = $categoryModel;
    }

    /**
     * Adds or removes categories to multiple contacts defined by contact ID.
     */
    public function execAction(Request $request): JsonResponse
    {
        $params = $request->get('lead_batch');
        $ids    = empty($params['ids']) ? [] : json_decode($params['ids']);

        if ($ids && is_array($ids)) {
            $categoriesToAdd    = $params['add'] ?? [];
            $categoriesToRemove = $params['remove'] ?? [];
            $contactIds         = json_decode($params['ids']);

            $this->actionModel->addContactsToCategories($contactIds, $categoriesToAdd);
            $this->actionModel->removeContactsFromCategories($contactIds, $categoriesToRemove);

            $this->addFlashMessage('mailvotech.lead.batch_leads_affected', [
                '%count%'     => count($ids),
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
     * View the modal form for adding contacts into categories in batches.
     */
    public function indexAction(): Response
    {
        $route = $this->generateUrl('mailvotech_category_batch_contact_set');
        $rows  = $this->categoryModel->getLookupResults('global', '', 300);
        $items = [];

        foreach ($rows as $category) {
            $items[$category['title'].' ('.$category['id'].')'] = $category['id'];
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
