<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Helper;

use MailVotech\Middleware\ConfigAwareTrait;

final class ParamsLoaderHelper
{
    use ConfigAwareTrait;

    private $parameters = [];

    /**
     * Get parameters for static method.
     *
     * @return array
     */
    public function getParameters()
    {
        if (empty($this->parameters)) {
            $this->parameters = $this->getConfig();
        }

        return $this->parameters;
    }
}
