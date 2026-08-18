<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Form\DataTransformer;

use MailVotech\LeadBundle\Entity\LeadField;
use MailVotech\LeadBundle\Entity\LeadFieldRepository;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @implements DataTransformerInterface<LeadField|null, int|null>
 */
final readonly class FieldToOrderTransformer implements DataTransformerInterface
{
    public function __construct(
        private LeadFieldRepository $leadFieldRepository,
    ) {
    }

    /**
     * Transforms an object to an integer (order).
     *
     * @param int|null $order
     *
     * @return LeadField|null
     */
    public function transform(mixed $order): mixed
    {
        if (!$order) {
            return null;
        }

        return $this->leadFieldRepository->findOneBy(['order' => $order]);
    }

    /**
     * Transforms a integer to an object.
     *
     * @param LeadField|null $field
     *
     * @return int|null
     */
    public function reverseTransform(mixed $field): mixed
    {
        if (null === $field) {
            return null;
        }

        return $field->getOrder();
    }
}
