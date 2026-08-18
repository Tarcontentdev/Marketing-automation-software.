<?php

namespace MailVotech\CoreBundle\Exception;

final class MessageOnlyErrorHandlerException extends ErrorHandlerException
{
    public function __construct($message = '')
    {
        parent::__construct($message, true);
    }
}
