<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Segment;

use MailVotech\LeadBundle\Provider\FilterOperatorProviderInterface;

class ContactSegmentFilterOperator
{
    public function __construct(
        private readonly FilterOperatorProviderInterface $filterOperatorProvider,
    ) {
    }

    /**
     * @param string $operator
     *
     * @return string
     */
    public function fixOperator($operator)
    {
        $options = $this->filterOperatorProvider->getAllOperators();

        if (empty($options[$operator])) {
            return $operator;
        }

        $operatorDetails = $options[$operator];

        return $operatorDetails['expr'];
    }
}
