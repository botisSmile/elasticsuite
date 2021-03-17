<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteAbCampaign
 * @author    Pierre Le Maguer <pierre.lemaguer@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteAbCampaign\Api;

use Exception;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignInterface;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignSearchResultsInterface;

/**
 * Interface CampaignRepositoryInterface
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
interface CampaignRepositoryInterface
{
    /**
     * Save campaign.
     *
     * @param CampaignInterface $item Campaign.
     * @return CampaignInterface
     * @throws AlreadyExistsException
     */
    public function save(CampaignInterface $item): CampaignInterface;

    /**
     * Delete campaign.
     *
     * @param CampaignInterface $item Campaign.
     * @return void
     * @throws Exception
     */
    public function delete(CampaignInterface $item): void;

    /**
     * Get a list of campaigns.
     *
     * @param SearchCriteriaInterface $searchCriteria Search criteria.
     * @return CampaignSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): CampaignSearchResultsInterface;

    /**
     * Get campaign by id.
     *
     * @param int $campaignId Campaign id.
     * @return CampaignInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $campaignId): CampaignInterface;

    /**
     * Delete campaign by id.
     *
     * @param int $campaignId Campaign id.
     * @return void
     * @throws Exception
     */
    public function deleteById(int $campaignId): void;
}
