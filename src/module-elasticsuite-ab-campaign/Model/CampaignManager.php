<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteAbCampaign
 * @author    Pierre LE MAGUER <pierre.lemaguer@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteAbCampaign\Model;

use Cassandra\Date;
use Exception;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\Filter\Date as DateFilter;
use Smile\ElasticsuiteAbCampaign\Api\CampaignRepositoryInterface;
use Smile\ElasticsuiteAbCampaign\Api\CampaignManagerInterface;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignInterface;
use Smile\ElasticsuiteAbCampaign\Model\ResourceModel\Campaign as CampaignResource;

/**
 * Class CampaignRepository
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class CampaignManager implements CampaignManagerInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var CampaignRepositoryInterface
     */
    private $campaignRepository;

    /**
     * @var CampaignResource
     */
    private $campaignResource;

    /**
     * @var DateFilter
     */
    private $dateFilter;

    /**
     * CampaignRepository constructor.
     *
     * @param CampaignRepositoryInterface $campaignRepository    Resource model.
     * @param SearchCriteriaBuilder       $searchCriteriaBuilder Search criteria builder
     * @param FilterBuilder               $filterBuilder         Filter builder
     * @param CampaignResource            $campaignResource      Campaign Resource
     * @param DateFilter                  $dateFilter            Date filter
     */
    public function __construct(
        CampaignRepositoryInterface $campaignRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        CampaignResource $campaignResource,
        DateFilter $dateFilter
    ) {
        $this->campaignRepository    = $campaignRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder         = $filterBuilder;
        $this->campaignResource      = $campaignResource;
        $this->dateFilter            = $dateFilter;
    }

    /**
     * {@inheritdoc}
     */
    public function getUnavailabilities(?CampaignInterface $campaign): array
    {
        /**
         * Get campaigns with status 'published' and different of the current compaign.
         */
        $this->searchCriteriaBuilder->addFilter(CampaignInterface::STATUS, CampaignInterface::STATUS_PUBLISHED, 'eq');
        if ($campaign && $campaign->getId()) {
            $this->searchCriteriaBuilder->addFilter(CampaignInterface::CAMPAIGN_ID, $campaign->getId(), 'neq');
        }

        $otherCampaigns = $this->campaignRepository->getList($this->searchCriteriaBuilder->create());
        $unavailabilities = [];
        /** @var CampaignInterface $otherCampaign */
        foreach ($otherCampaigns->getItems() as $otherCampaign) {
            if (!$otherCampaign->getStartDate() || !$otherCampaign->getEndDate()) {
                throw new LocalizedException(
                    __('A campaign is actually published without start or end date.'
                        . ' Please contact your technical support to fix this issue.')
                );
            }
            $unavailabilities[] = [
                'start_date' => $otherCampaign->getStartDate(),
                'end_date' => $otherCampaign->getEndDate(),
            ];
        }

        return $unavailabilities;
    }

    /**
     * {@inheritDoc}
     */
    public function stopCampaign(CampaignInterface $campaign)
    {
        if (!$this->canStop($campaign)) {
            throw new LocalizedException(__("You can't stop this campaign"));
        }

        $now = $this->dateFilter->filter(new \DateTime('now'));
        $newStatus = CampaignInterface::STATUS_DRAFT;
        if ($now >= $campaign->getStartDate()) {
            $newStatus = CampaignInterface::STATUS_COMPLETE;
            $this->campaignResource->updateEndDate($now, $campaign->getId());
        }

        $this->campaignResource->updateStatus($newStatus, $campaign->getId());
    }

    /**
     * {@inheritDoc}
     */
    public function publishCampaign(CampaignInterface $campaign)
    {
        if (!$this->canPublish($campaign)) {
            throw new LocalizedException(__("You can't publish this campaign"));
        }

        $this->campaignResource->updateStatus(CampaignInterface::STATUS_PUBLISHED, $campaign->getId());
    }

    /**
     * {@inheritDoc}
     */
    public function reopenCampaign(CampaignInterface $campaign)
    {
        if (!$this->canReopen($campaign)) {
            throw new LocalizedException(__("You can't reopen this campaign"));
        }

        $this->campaignResource->updateStatus(CampaignInterface::STATUS_DRAFT, $campaign->getId());
    }

    /**
     * {@inheritDoc}
     */
    public function canPublish(CampaignInterface $campaign): bool
    {
        return $campaign->getStatus() === CampaignInterface::STATUS_DRAFT;
    }

    /**
     * {@inheritDoc}
     */
    public function canReopen(CampaignInterface $campaign): bool
    {
        return $campaign->getStatus() === CampaignInterface::STATUS_COMPLETE;
    }

    /**
     * {@inheritDoc}
     */
    public function canStop(CampaignInterface $campaign): bool
    {
        return $campaign->getStatus() === CampaignInterface::STATUS_PUBLISHED;
    }
}
