<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Model\AbTest;

use MailVotech\CoreBundle\Entity\VariantEntityInterface;
use MailVotech\CoreBundle\Event\DetermineWinnerEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class AbTestResultService
{
    public function __construct(
        private EventDispatcherInterface $dispatcher,
    ) {
    }

    /**
     * @param mixed[] $criteria
     *
     * @return array{winners?: array<int, int|string>, support?: mixed, basedOn?: string, supportTemplate?: string}
     */
    public function getAbTestResult(VariantEntityInterface $parentVariant, array $criteria = []): ?array
    {
        // get A/B test information
        [$parent, $children] = $parentVariant->getVariants();

        $abTestResults = [];
        if ([] !== $criteria) {
            $testSettings = $criteria;
            $args         = [
                'email'    => $parentVariant,
                'parent'   => $parent,
                'children' => $children,
            ];

            if (isset($testSettings['event'])) {
                $determineWinnerEvent = new DetermineWinnerEvent($args);
                $this->dispatcher->dispatch($determineWinnerEvent, $testSettings['event']);
                $abTestResults = $determineWinnerEvent->getAbTestResults();
            }
        }

        return $abTestResults;
    }
}
