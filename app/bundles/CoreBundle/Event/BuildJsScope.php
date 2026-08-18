<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Event;

enum BuildJsScope
{
    case RUNTIME;
    case ESSENTIAL;
    case TRACKING;
}
