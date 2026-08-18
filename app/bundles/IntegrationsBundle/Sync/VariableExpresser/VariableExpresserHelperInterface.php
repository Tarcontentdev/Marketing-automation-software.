<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Sync\VariableExpresser;

use MailVotech\IntegrationsBundle\Sync\DAO\Value\EncodedValueDAO;
use MailVotech\IntegrationsBundle\Sync\DAO\Value\NormalizedValueDAO;

interface VariableExpresserHelperInterface
{
    public function decodeVariable(EncodedValueDAO $EncodedValueDAO): NormalizedValueDAO;

    /**
     * @param mixed $var
     */
    public function encodeVariable($var): EncodedValueDAO;
}
