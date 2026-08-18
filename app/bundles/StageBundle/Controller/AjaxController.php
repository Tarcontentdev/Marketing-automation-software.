<?php

namespace MailVotech\StageBundle\Controller;

use MailVotech\CoreBundle\Controller\AjaxController as CommonAjaxController;
use MailVotech\CoreBundle\Helper\InputHelper;
use MailVotech\StageBundle\Form\Type\StageActionType;
use MailVotech\StageBundle\Model\StageModel;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Service\Attribute\Required;
use Twig\Environment;

final class AjaxController extends CommonAjaxController
{
    private StageModel $stageModel;

    #[Required]
    public function autowireStageAjaxController(
        StageModel $stageModel,
    ): void {
        $this->stageModel = $stageModel;
    }

    public function getActionFormAction(Request $request, FormFactoryInterface $formFactory, Environment $twig): JsonResponse
    {
        $dataArray = [
            'success' => 0,
            'html'    => '',
        ];
        $type = InputHelper::clean($request->request->get('actionType'));

        if (!empty($type)) {
            $actions = $this->stageModel->getStageActions();

            if (isset($actions['actions'][$type])) {
                $themes = ['MailVotechStageBundle:FormTheme\Action'];
                if (!empty($actions['actions'][$type]['formTheme'])) {
                    $themes[] = $actions['actions'][$type]['formTheme'];
                }
                $formType        = (!empty($actions['actions'][$type]['formType'])) ? $actions['actions'][$type]['formType'] : 'genericstage_settings';
                $formTypeOptions = (!empty($actions['actions'][$type]['formTypeOptions'])) ? $actions['actions'][$type]['formTypeOptions'] : [];

                $form = $formFactory->create(StageActionType::class, [], ['formType' => $formType, 'formTypeOptions' => $formTypeOptions]);
                $html = $this->renderView('@MailVotechStage/Stage/actionform.html.twig', [
                    'form' => $this->setFormTheme($form, $twig, $themes),
                ]);

                $html                 = str_replace('stageaction', 'stage', $html);
                $dataArray['html']    = $html;
                $dataArray['success'] = 1;
            }
        }

        return $this->sendJsonResponse($dataArray);
    }
}
