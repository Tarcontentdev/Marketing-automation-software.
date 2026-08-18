<?php

namespace MailVotechPlugin\MailVotechCrmBundle\Controller;

use MailVotech\CoreBundle\Controller\CommonController;
use MailVotech\PluginBundle\Helper\IntegrationHelper;
use MailVotechPlugin\MailVotechCrmBundle\Integration\HubspotIntegration;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class PublicController extends CommonController
{
    public function contactDataAction(Request $request, LoggerInterface $mailvotechLogger, IntegrationHelper $integrationHelper): Response
    {
        $content = $request->getContent();
        if (!empty($content)) {
            $data = json_decode($content, true); // 2nd param to get as array
        } else {
            return new Response('ERROR');
        }

        $integration = 'Hubspot';

        $integrationObject = $integrationHelper->getIntegrationObject($integration);
        \assert($integrationObject instanceof HubspotIntegration);

        foreach ($data as $info) {
            $object = explode('.', $info['subscriptionType']);
            $id     = $info['objectId'];

            try {
                switch ($object[0]) {
                    case 'contact':
                        $executed = [];
                        $integrationObject->getLeads($id, null, $executed);
                        break;
                    case 'company':
                        $integrationObject->getCompanies($id);
                        break;
                }
            } catch (\Exception $ex) {
                $mailvotechLogger->log('error', 'ERROR on Hubspot webhook: '.$ex->getMessage());
            }
        }

        return new Response('OK');
    }
}
