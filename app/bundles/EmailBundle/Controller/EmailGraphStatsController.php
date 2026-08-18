<?php

namespace MailVotech\EmailBundle\Controller;

use MailVotech\CoreBundle\Form\Type\DateRangeType;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\EmailBundle\Model\EmailModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class EmailGraphStatsController extends AbstractController
{
    /**
     * Loads a specific form into the detailed panel.
     *
     * @param int    $objectId
     * @param bool   $isVariant
     * @param string $dateFrom
     * @param string $dateTo
     *
     * @throws \Exception
     */
    public function viewAction(
        Request $request,
        EmailModel $model,
        FormFactoryInterface $formFactory,
        CorePermissions $security,
        $objectId,
        $isVariant,
        $dateFrom = null,
        $dateTo = null,
    ): Response {
        /** @var \MailVotech\EmailBundle\Entity\Email $email */
        $email = $model->getEntity($objectId);

        // Init the date range filter form
        $dateRangeValues = ['date_from' => $dateFrom, 'date_to' => $dateTo];
        $action          = $this->generateUrl('mailvotech_email_action', ['objectAction' => 'view', 'objectId' => $objectId]);
        $dateRangeForm   = $formFactory->create(DateRangeType::class, $dateRangeValues, ['action' => $action]);

        if (null === $email || !$security->hasEntityAccess(
            'email:emails:viewown',
            'email:emails:viewother',
            $email->getCreatedBy()
        )) {
            throw new AccessDeniedHttpException();
        }

        // get A/B test information
        [$parent, $children] = $email->getVariants();

        // get related translations
        [$translationParent, $translationChildren] = $email->getTranslations();

        // Prepare stats for bargraph
        if ($chartStatsSource = $request->query->get('stats')) {
            $includeVariants = ('all' === $chartStatsSource);
        } else {
            $includeVariants = (($email->isVariant() && $parent === $email) || ($email->isTranslation() && $translationParent === $email));
        }

        $dateFromObject = new \DateTime($dateFrom);
        $dateToObject   = new \DateTime($dateTo);

        if ('template' === $email->getEmailType()) {
            $stats = $model->getEmailGeneralStats(
                $email,
                $includeVariants,
                null,
                $dateFromObject,
                $dateToObject
            );
        } else {
            $stats = $model->getEmailListStats(
                $email,
                $includeVariants,
                $dateFromObject,
                $dateToObject
            );
        }

        $statsDevices = $model->getEmailDeviceStats(
            $email,
            $includeVariants,
            $dateFromObject,
            $dateToObject
        );

        return $this->render(
            '@MailVotechEmail/Email/graph.html.twig',
            [
                'email'         => $email,
                'stats'         => $stats,
                'statsDevices'  => $statsDevices,
                'showAllStats'  => $includeVariants,
                'dateRangeForm' => $dateRangeForm->createView(),
                'isVariant'     => $isVariant,
            ]
        );
    }
}
