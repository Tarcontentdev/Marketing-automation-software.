<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Sync\DAO\Sync\Order;

use MailVotech\IntegrationsBundle\Sync\DAO\Value\NormalizedValueDAO;

final class FieldDAO
{
    /**
     * @param string $name
     */
    public function __construct(
        private $name,
        private readonly NormalizedValueDAO $value,
    ) {
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function getValue(): NormalizedValueDAO
    {
        return $this->value;
    }
}
