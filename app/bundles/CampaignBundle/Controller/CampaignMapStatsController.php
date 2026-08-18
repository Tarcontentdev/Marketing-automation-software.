<?php

declare(strict_types=1);

namespace MailVotech\CampaignBundle\Controller;

use Doctrine\DBAL\Exception;
use MailVotech\CampaignBundle\Entity\Campaign;
use MailVotech\CampaignBundle\Model\CampaignModel;
use MailVotech\CoreBundle\Helper\MapHelper;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class CampaignMapStatsController extends AbstractController
{
    public const MAP_OPTIONS = [
        'contacts' => [
            'label' => 'mailvotech.lead.leads',
            'unit'  => 'Contact',
        ],
        'read_count' => [
            'label' => 'mailvotech.email.read',
            'unit'  => 'Read',
        ],
        'clicked_through_count'=> [
            'label' => 'mailvotech.email.click',
            'unit'  => 'Click',
        ],
    ];

    public const LEGEND_TEXT = 'Total: %total (%withCountry with country)';

    public function __construct(
        private readonly CampaignModel $model,
    ) {
    }

    /**
     * @return array<string, array<int, array<string, int|string>>>
     *
     * @throws Exception
     */
    public function getData(Campaign $entity, \DateTimeImmutable $dateFromObject, \DateTimeImmutable $dateToObject): array
    {
        return $this->model->getCountryStats($entity, $dateFromObject, $dateToObject);
    }

    public function hasAccess(CorePermissions $security, Campaign $entity): bool
    {
        return $security->hasEntityAccess(
            'email:emails:viewown',
            'email:emails:viewother',
            $entity->getCreatedBy()
        );
    }

    /**
     * @return array<string,array<string, string>>
     */
    public function getMapOptions(Campaign $entity): array
    {
        if ($entity->isEmailCampaign()) {
            return self::MAP_OPTIONS;
        }

        $key = array_key_first(self::MAP_OPTIONS);

        return [$key => self::MAP_OPTIONS[$key]];
    }

    public function getMapOptionsTitle(): string
    {
        return '';
    }

    /**
     * @throws \Exception
     */
    public function viewAction(
        CorePermissions $security,
        int $objectId,
        string $dateFrom = '',
        string $dateTo = '',
    ): Response {
        $entity = $this->model->getEntity($objectId);

        if (!$entity instanceof Campaign || !$this->hasAccess($security, $entity)) {
            throw new AccessDeniedHttpException();
        }

        $statsCountries = $this->getData($entity, new \DateTimeImmutable($dateFrom), new \DateTimeImmutable($dateTo));
        $mapData        = MapHelper::buildMapData($statsCountries, $this->getMapOptions($entity), self::LEGEND_TEXT);

        return $this->render(
            '@MailVotechCore/Helper/map.html.twig',
            [
                'data'           => $mapData[0]['data'],
                'height'         => 315,
                'optionsEnabled' => true,
                'optionsTitle'   => $this->getMapOptionsTitle(),
                'options'        => $mapData,
                'legendEnabled'  => true,
                'statUnit'       => $mapData[0]['unit'],
            ]
        );
    }
}
