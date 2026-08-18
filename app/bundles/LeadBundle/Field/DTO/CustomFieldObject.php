<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Field\DTO;

use MailVotech\LeadBundle\Entity\LeadField;
use MailVotech\LeadBundle\Exception\InvalidObjectTypeException;

final class CustomFieldObject
{
    private array $objects = [
        'lead'    => 'leads',
        'company' => 'companies',
    ];

    private readonly LeadField $leadField;

    /**
     * @throws InvalidObjectTypeException
     */
    public function __construct(LeadField $leadField)
    {
        $leadFieldObject = $leadField->getObject();
        if (!isset($this->objects[$leadFieldObject])) {
            throw new InvalidObjectTypeException($leadFieldObject.' has no associated object.');
        }

        $this->leadField = $leadField;
    }

    public function getObject(): string
    {
        return $this->objects[$this->leadField->getObject()];
    }
}
