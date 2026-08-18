<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Helper\Update\PreUpdateChecks;

final class CheckPhpVersion extends AbstractPreUpdateCheck
{
    public function runCheck(): PreUpdateCheckResult
    {
        $metadata = $this->getUpdateCandidateMetadata();

        if (
            version_compare(PHP_VERSION, $metadata->getMinSupportedPHPVersion(), 'lt')
            || version_compare(PHP_VERSION, $metadata->getMaxSupportedPHPVersion(), 'gt')
        ) {
            return new PreUpdateCheckResult(false, $this, [
                new PreUpdateCheckError('mailvotech.core.update.check.phpversion', [
                    '%currentversion%' => PHP_VERSION,
                    '%lowestversion%'  => $metadata->getMinSupportedPHPVersion(),
                    '%highestversion%' => $metadata->getMaxSupportedPHPVersion(),
                ]),
            ]);
        }

        return new PreUpdateCheckResult(true, $this);
    }
}
