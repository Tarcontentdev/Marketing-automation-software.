<?php

namespace MailVotech\PageBundle\Controller;

use MailVotech\CoreBundle\Controller\AjaxController as CommonAjaxController;
use MailVotech\CoreBundle\Controller\VariantAjaxControllerTrait;
use MailVotech\CoreBundle\Helper\InputHelper;
use MailVotech\PageBundle\Form\Type\AbTestPropertiesType;
use MailVotech\PageBundle\Model\PageModel;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Service\Attribute\Required;
use Twig\Environment;

final class AjaxController extends CommonAjaxController
{
    use VariantAjaxControllerTrait;

    private PageModel $pageModel;

    #[Required]
    public function autowirePageAjaxController(
        PageModel $pageModel,
    ): void {
        $this->pageModel = $pageModel;
    }

    public function getAbTestFormAction(Request $request, FormFactoryInterface $formFactory, PageModel $pageModel, Environment $twig): JsonResponse
    {
        return $this->sendJsonResponse($this->getAbTestForm(
            $request,
            $pageModel,
            fn ($formType, $formOptions): FormInterface => $formFactory->create(AbTestPropertiesType::class, [], ['formType' => $formType, 'formTypeOptions' => $formOptions]),
            fn (FormInterface $form): string => $this->renderView('@MailVotechPage/AbTest/form.html.twig', ['form' => $this->setFormTheme($form, $twig, ['@MailVotechPage/AbTest/form.html.twig', 'MailVotechPageBundle:FormTheme\Page'])]),
            'page_abtest_settings',
            'page'
        ));
    }

    public function pageListAction(Request $request): JsonResponse
    {
        $filter    = InputHelper::clean($request->query->get('filter'));
        $results   = $this->pageModel->getLookupResults('page', $filter);
        $dataArray = [];

        foreach ($results as $r) {
            $dataArray[] = [
                'label' => $r['title']." ({$r['id']}:{$r['alias']})",
                'value' => $r['id'],
            ];
        }

        return $this->sendJsonResponse($dataArray);
    }

    /**
     * Called by parent::getBuilderTokensAction().
     *
     * @return array
     */
    protected function getBuilderTokens($query)
    {
        return $this->pageModel->getBuilderComponents(null, ['tokens'], $query ?? '');
    }
}
