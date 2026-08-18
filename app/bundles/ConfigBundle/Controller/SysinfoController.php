<?php

namespace MailVotech\ConfigBundle\Controller;

use MailVotech\ConfigBundle\Model\SysinfoModel;
use MailVotech\CoreBundle\Controller\FormController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\Required;

final class SysinfoController extends FormController
{
    private SysinfoModel $sysinfoModel;

    #[Required]
    public function autowireSysinfoController(
        SysinfoModel $sysinfoModel,
    ): void {
        $this->sysinfoModel = $sysinfoModel;
    }

    public function indexAction(): Response
    {
        if (!$this->user->isAdmin() || $this->coreParametersHelper->get('sysinfo_disabled')) {
            $this->throwAccessDenied();
        }

        return $this->delegateView([
            'viewParameters' => [
                'phpInfo'         => $this->sysinfoModel->getPhpInfo(),
                'requirements'    => $this->sysinfoModel->getRequirements(),
                'recommendations' => $this->sysinfoModel->getRecommendations(),
                'folders'         => $this->sysinfoModel->getFolders(),
                'log'             => $this->sysinfoModel->getLogTail(200),
                'dbInfo'          => $this->sysinfoModel->getDbInfo(),
            ],
            'contentTemplate' => '@MailVotechConfig/Sysinfo/index.html.twig',
            'passthroughVars' => [
                'activeLink'    => '#mailvotech_sysinfo_index',
                'mailvotechContent' => 'sysinfo',
                'route'         => $this->generateUrl('mailvotech_sysinfo_index'),
            ],
        ]);
    }
}
