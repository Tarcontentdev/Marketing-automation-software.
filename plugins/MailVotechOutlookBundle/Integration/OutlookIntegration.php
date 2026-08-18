<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechOutlookBundle\Integration;

use MailVotech\CoreBundle\Helper\UrlHelper;
use MailVotech\PluginBundle\Integration\AbstractIntegration;

final class OutlookIntegration extends AbstractIntegration
{
    public function getName(): string
    {
        return 'Outlook';
    }

    /**
     * Return's authentication method such as oauth2, oauth1a, key, etc.
     */
    public function getAuthenticationType(): string
    {
        // Just use none for now and I'll build in "basic" later
        return 'none';
    }

    /**
     * Return array of key => label elements that will be converted to inputs to
     * obtain from the user.
     */
    public function getRequiredKeyFields(): array
    {
        return [
            'secret' => 'mailvotech.integration.outlook.secret',
        ];
    }

    /**
     * @return array<mixed>
     */
    public function getFormNotes($section)
    {
        if ('custom' === $section) {
            return [
                'template'   => '@MailVotechOutlook/Integration/form.html.twig',
                'parameters' => [
                    'mailvotechUrl' => UrlHelper::rel2abs('/index.php'),
                ],
            ];
        }

        return parent::getFormNotes($section);
    }
}
