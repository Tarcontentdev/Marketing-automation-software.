<?php

namespace MailVotech\LeadBundle\Segment\Stat;

use Doctrine\ORM\EntityManagerInterface;
use MailVotech\CampaignBundle\Entity\CampaignRepository;
use MailVotech\CoreBundle\Helper\CacheStorageHelper;

final readonly class SegmentCampaignShare
{
    public function __construct(
        private CacheStorageHelper $cacheStorageHelper,
        private EntityManagerInterface $entityManager,
        private CampaignRepository $campaignRepository,
    ) {
    }

    /**
     * @param mixed[] $campaignIds
     *
     * @return mixed[]
     */
    public function getCampaignsSegmentShare(int $segmentId, array $campaignIds = []): array
    {
        $campaigns = $this->campaignRepository->getCampaignsSegmentShare($segmentId, $campaignIds);
        foreach ($campaigns as $campaign) {
            $this->cacheStorageHelper->set($this->getCachedKey($segmentId, $campaign['id']), $campaign['segmentCampaignShare']);
        }

        return $campaigns;
    }

    /**
     * @param int $segmentId
     *
     * @return array
     */
    public function getCampaignList($segmentId)
    {
        $q = $this->entityManager->getConnection()->createQueryBuilder();
        $q->select('c.id, c.name, null as share')
            ->from(MAILVOTECH_TABLE_PREFIX.'campaigns', 'c')
            ->where($this->campaignRepository->getPublishedByDateDbalExpression($q))
            ->orderBy('c.id', 'DESC');

        $campaigns = $q->executeQuery()->fetchAllAssociative();

        foreach ($campaigns as &$campaign) {
            // just load from cache If exists
            if ($share  = $this->cacheStorageHelper->get($this->getCachedKey($segmentId, $campaign['id']))) {
                $campaign['share'] = $share;
            }
        }

        return $campaigns;
    }

    /**
     * @param int $segmentId
     * @param int $campaignId
     */
    private function getCachedKey($segmentId, $campaignId): string
    {
        return sprintf('%s|%s|%s|%s|%s', 'campaign', $campaignId, 'segment', $segmentId, 'share');
    }
}
