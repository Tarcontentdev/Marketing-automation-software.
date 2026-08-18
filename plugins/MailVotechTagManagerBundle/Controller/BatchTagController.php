<?php

namespace MailVotechPlugin\MailVotechTagManagerBundle\Controller;

use MailVotech\CoreBundle\Controller\AbstractFormController;
use MailVotechPlugin\MailVotechTagManagerBundle\Entity\TagRepository;
use MailVotechPlugin\MailVotechTagManagerBundle\Form\Type\BatchTagType;
use MailVotechPlugin\MailVotechTagManagerBundle\Model\TagModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\Required;

final class BatchTagController extends AbstractFormController
{
    private TagRepository $tagRepository;

    #[Required]
    public function autowireBatchTagController(
        TagModel $tagModel,
        TagRepository $tagRepository,
    ): void {
        $this->tagRepository = $tagRepository;
    }

    public function indexAction(): Response
    {
        $route = $this->generateUrl('mailvotech_tagmanager_batch_set_action');

        $form = $this->createForm(BatchTagType::class, [],
            [
                'action' => $route,
            ]
        )->createView();

        // set some permissions
        $permissions = $this->security->isGranted([
            'tagManager:tagManager:view',
            'tagManager:tagManager:edit',
            'tagManager:tagManager:create',
            'tagManager:tagManager:delete',
        ], 'RETURN_ARRAY');

        if (!$permissions['tagManager:tagManager:view']) {
            $this->throwAccessDenied();
        }

        return $this->delegateView([
            'viewParameters'  => [
                'form' => $form,
            ],
            'contentTemplate' => '@MailVotechLead/Batch/form.html.twig',
            'passthroughVars' => [
                'activeLink'    => '#mailvotech_tagmanager_batch_index_action',
                'mailvotechContent' => 'tagBatch',
                'route'         => $route,
            ],
        ]);
    }

    public function execAction(Request $request): JsonResponse
    {
        $params   = $request->get('batch_tag');
        $ids    = empty($params['ids']) ? [] : json_decode($params['ids']);
        if (empty($ids)) {
            $this->addFlashMessage('mailvotech.core.error.ids.missing');

            return new JsonResponse([
                'closeModal' => true,
                'flashes'    => $this->getFlashContent(),
            ]);
        }

        $tagsToAdd    = [];
        $tagsToRemove = [];
        if (isset($params['tags']['add_tags']) && !empty($params['tags']['add_tags'])) {
            $tagsToAdd = $params['tags']['add_tags'];
        }
        if (isset($params['tags']['remove_tags']) && !empty($params['tags']['remove_tags'])) {
            $tagsToRemove = $params['tags']['remove_tags'];
        }
        if (
            empty($tagsToAdd) && empty($tagsToRemove)
        ) {
            $this->addFlashMessage('mailvotech.core.error.nothing.to.save');

            return new JsonResponse([
                'closeModal' => true,
                'flashes'    => $this->getFlashContent(),
            ]);
        }

        if (!empty($tagsToAdd)) {
            $this->tagRepository->addTagsToLeads($ids, $tagsToAdd);
        }

        if (!empty($tagsToRemove)) {
            $this->tagRepository->removeTagsFromLeads($ids, $tagsToRemove);
        }

        $this->addFlashMessage('mailvotech.lead.batch_leads_affected', [
            '%count%'     => count($ids),
        ]);

        return new JsonResponse([
            'closeModal' => true,
            'flashes'    => $this->getFlashContent(),
        ]);
    }
}
