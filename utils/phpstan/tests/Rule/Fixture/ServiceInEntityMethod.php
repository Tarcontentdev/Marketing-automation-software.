<?php

declare(strict_types=1);

namespace Utils\PHPStan\Tests\Rule\Fixture;

use MailVotech\CoreBundle\Entity\CommonEntity;
use MailVotech\CoreBundle\Translation\Translator;

final class ServiceInEntityMethod extends CommonEntity
{
    /**
     * @return string[]
     */
    public function getRowStatusesPieChart(Translator $translator): array
    {
        return [$translator->trans('mailvotech.core.success')];
    }
}
