<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Service;

use Doctrine\ORM\Tools\Pagination\Paginator;
use MailVotech\CoreBundle\DTO\GlobalSearchFilterDTO;
use MailVotech\CoreBundle\Event\GlobalSearchEvent;
use MailVotech\CoreBundle\Model\GlobalSearchInterface;
use Twig\Environment;

class GlobalSearch
{
    public function __construct(
        private readonly Environment $twig,
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function performSearch(
        GlobalSearchFilterDTO $filterDTO,
        GlobalSearchInterface $model,
        string $template,
    ): array {
        if (empty($filterDTO->getSearchString()) || (!$model->canViewOwnEntity() && !$model->canViewOthersEntity())) {
            return [];
        }

        $entities = $model->getEntitiesForGlobalSearch($filterDTO);

        if (null === $entities || ($entities instanceof Paginator && empty($entities->count()))) {
            return [];
        }

        return $this->processResults($entities, $filterDTO->getSearchString(), $template);
    }

    /**
     * @return array<int|string, int|string>
     */
    private function processResults(Paginator $entities, string $searchString, string $template): array
    {
        $count           = $entities->count();
        $renderedResults = [];
        foreach ($entities as $entity) {
            $renderedResults[] = $this->twig->render($template, ['item' => $entity]);
        }

        if ($count > GlobalSearchEvent::RESULTS_LIMIT) {
            $renderedResults[] = $this->twig->render($template, [
                'searchString' => $searchString,
                'showMore'     => true,
                'remaining'    => $count - GlobalSearchEvent::RESULTS_LIMIT,
            ]);
        }

        $renderedResults['count'] = $count;

        return $renderedResults;
    }
}
