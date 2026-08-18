<?php

namespace MailVotechPlugin\MailVotechFocusBundle\Controller;

use MailVotech\CacheBundle\Cache\CacheProviderTagAwareInterface;
use MailVotech\CoreBundle\Controller\AbstractStandardFormController;
use MailVotech\CoreBundle\Form\Type\DateRangeType;
use MailVotech\PageBundle\Model\TrackableModel;
use MailVotechPlugin\MailVotechFocusBundle\Entity\Focus;
use MailVotechPlugin\MailVotechFocusBundle\Model\FocusModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\Required;

final class FocusController extends AbstractStandardFormController
{
    private CacheProviderTagAwareInterface $cacheProvider;

    private FocusModel $focusModel;

    private TrackableModel $trackableModel;

    #[Required]
    public function autowireFocusController(
        CacheProviderTagAwareInterface $cacheProvider,
        FocusModel $focusModel,
        TrackableModel $trackableModel,
    ): void {
        $this->cacheProvider = $cacheProvider;
        $this->focusModel = $focusModel;
        $this->trackableModel = $trackableModel;
    }

    protected function getTemplateBase(): string
    {
        return '@MailVotechFocus/Focus';
    }

    protected function getModelName(): string
    {
        return 'focus';
    }

    /**
     * @param int $page
     */
    public function indexAction(Request $request, $page = 1): Response
    {
        return parent::indexStandard($request, $page);
    }

    /**
     * Generates new form and processes post data.
     */
    public function newAction(Request $request): Response
    {
        return parent::newStandard($request);
    }

    /**
     * Generates edit form and processes post data.
     *
     * @param int  $objectId
     * @param bool $ignorePost
     */
    public function editAction(Request $request, $objectId, $ignorePost = false): Response
    {
        return parent::editStandard($request, $objectId, $ignorePost);
    }

    /**
     * Displays details on a Focus.
     */
    public function viewAction(Request $request, $objectId): Response
    {
        return parent::viewStandard($request, $objectId, 'focus', 'focus');
    }

    /**
     * Clone an entity.
     *
     * @param int $objectId
     */
    public function cloneAction(Request $request, $objectId): Response
    {
        return parent::cloneStandard($request, $objectId);
    }

    /**
     * Deletes the entity.
     *
     * @param int $objectId
     */
    public function deleteAction(Request $request, $objectId): Response
    {
        return parent::deleteStandard($request, $objectId);
    }

    /**
     * Deletes a group of entities.
     */
    public function batchDeleteAction(Request $request): Response
    {
        return parent::batchDeleteStandard($request);
    }

    /**
     * @throws \Exception
     */
    public function getViewArguments(array $args, $action): array
    {
        $cacheTimeout = (int) $this->coreParametersHelper->get('cached_data_timeout');

        if ('view' == $action) {
            /** @var Focus $item */
            $item = $args['viewParameters']['item'];

            // For line graphs in the view
            $dateRangeValues = $this->getCurrentRequest()->get('daterange', []);
            $dateRangeForm   = $this->formFactory->create(
                DateRangeType::class,
                $dateRangeValues,
                [
                    'action' => $this->generateUrl(
                        'mailvotech_focus_action',
                        [
                            'objectAction' => 'view',
                            'objectId'     => $item->getId(),
                        ]
                    ),
                ]
            );

            $statsDateFrom = new \DateTime($dateRangeForm->get('date_from')->getData());
            $statsDateTo   = new \DateTime($dateRangeForm->get('date_to')->getData());
            $cacheKey      = "focus.viewArguments.{$item->getId()}.{$statsDateFrom->getTimestamp()}.{$statsDateTo->getTimestamp()}";
            $cacheItem     = $this->cacheProvider->getItem($cacheKey);

            if ($cacheItem->isHit()) {
                [$stats, $trackables] = $cacheItem->get();
            } else {
                // invalidate cache for entire focus item to keep AJAX loaded data consistent
                $this->cacheProvider->invalidateTags(["focus.{$item->getId()}"]);
                $stats = $this->focusModel->getStats(
                    $item,
                    null,
                    $statsDateFrom,
                    $statsDateTo
                );

                if ('link' === $item->getType()) {
                    $trackables = $this->trackableModel->getTrackableList('focus', $item->getId());

                    $cacheItem->set([$stats, $trackables]);
                    $cacheItem->expiresAfter($cacheTimeout * 60);
                    $cacheItem->tag("focus.{$item->getId()}");
                    $this->cacheProvider->save($cacheItem);
                }
            }

            $args['viewParameters']['stats']                 = $stats;
            $args['viewParameters']['dateRangeForm']         = $dateRangeForm->createView();
            $args['viewParameters']['showConversionRate']    = true;
            if (isset($trackables)) {
                $args['viewParameters']['trackables'] = $trackables;
            }
        }

        return $args;
    }

    /**
     * @return mixed[]
     */
    protected function getPostActionRedirectArguments(array $args, $action): array
    {
        $focus        = $this->getCurrentRequest()->request->all()['focus'] ?? [];
        $updateSelect = 'POST' === $this->getCurrentRequest()->getMethod()
            ? ($focus['updateSelect'] ?? false)
            : $this->getCurrentRequest()->get('updateSelect', false);

        if ($updateSelect) {
            switch ($action) {
                case 'new':
                case 'edit':
                    $passthrough = $args['passthroughVars'];
                    $passthrough = array_merge(
                        $passthrough,
                        [
                            'updateSelect' => $updateSelect,
                            'id'           => $args['entity']->getId(),
                            'name'         => $args['entity']->getName(),
                        ]
                    );
                    $args['passthroughVars'] = $passthrough;
                    break;
            }
        }

        return $args;
    }

    protected function getEntityFormOptions(): array
    {
        $focus        = $this->getCurrentRequest()->request->all()['focus'] ?? [];
        $updateSelect = 'POST' === $this->getCurrentRequest()->getMethod()
            ? ($focus['updateSelect'] ?? false)
            : $this->getCurrentRequest()->get('updateSelect', false);

        if ($updateSelect) {
            return ['update_select' => $updateSelect];
        }

        return [];
    }

    /**
     * Return array of options update select response.
     *
     * @param string $updateSelect HTML id of the select
     * @param object $entity
     * @param string $nameMethod   name of the entity method holding the name
     * @param string $groupMethod  name of the entity method holding the select group
     */
    protected function getUpdateSelectParams($updateSelect, $entity, $nameMethod = 'getName', $groupMethod = 'getLanguage'): array
    {
        return [
            'updateSelect' => $updateSelect,
            'id'           => $entity->getId(),
            'name'         => $entity->{$nameMethod}(),
        ];
    }
}
