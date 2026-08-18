<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\Controller;

use MailVotech\CoreBundle\Controller\AbstractFormController;
use MailVotech\CoreBundle\Model\AbTest\AbTestSettingsService;
use MailVotech\EmailBundle\Entity\Email;
use MailVotech\EmailBundle\Form\Type\GenerateABTestType;
use MailVotech\EmailBundle\Model\EmailModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ABTestController extends AbstractFormController
{
    public const DEFAULT_DELAY = 24;

    public const TOTAL_WEIGHT = 10;

    public function generateABTestAction(Request $request, EmailModel $emailModel, int $objectId): Response
    {
        if (!$parent = $emailModel->getEntity($objectId)) {
            return $this->notFound();
        }

        if (!$this->security->hasEntityAccess(
            'email:emails:editown',
            'email:emails:editother',
            $parent->getCreatedBy()
        )) {
            $this->throwAccessDenied();
        }

        $action = $this->generateUrl('mailvotech_abtest_generate', ['objectId' => $objectId]);
        $data1  = $parent->getVariantSettings();
        $form   =  $this->createForm(GenerateABTestType::class, $data1, ['action' => $action]);

        if ('POST' === $request->getMethod()) {
            $isCancelled    = $this->isFormCancelled($form);
            $isValid        = $this->isFormValid($form);
            $data           = $form->getData();

            if (!$isCancelled && $isValid) {
                $this->updateExistingParentVariant($parent, $data, $emailModel);
            }

            if ($isCancelled || $isValid) {
                $viewParameters = [
                    'objectAction' => 'view',
                    'objectId'     => $objectId,
                ];

                return $this->postActionRedirect(
                    [
                        'returnUrl'       => $this->generateUrl('mailvotech_email_action', $viewParameters),
                        'viewParameters'  => $viewParameters,
                        'contentTemplate' => 'MailVotech\EmailBundle\Controller\EmailController::viewAction',
                        'passthroughVars' => [
                            'mailvotechContent' => 'email',
                            'closeModal'    => 1,
                        ],
                    ]
                );
            }
        }

        return $this->delegateView(
            [
                'viewParameters' => [
                    'form' => $form->createView(),
                ],
                'contentTemplate' => '@MailVotechEmail/Email/abtest.html.twig',
            ]
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function updateExistingParentVariant(Email $parent, array $data, EmailModel $emailModel): void
    {
        $variantSettings                    = $parent->getVariantSettings();
        $variantSettings['winnerCriteria']  = $data['winnerCriteria'] ?? 'email.openrate';
        $variantSettings['sendWinnerDelay'] = $data['sendWinnerDelay'] ?? self::DEFAULT_DELAY;
        $variantSettings['totalWeight']     = $data['totalWeight'] ?? AbTestSettingsService::DEFAULT_AB_WEIGHT;
        $variantSettings['enableAbTest']    = 1;

        $parent->setVariantSettings($variantSettings);

        $emailModel->saveEntity($parent);
    }
}
