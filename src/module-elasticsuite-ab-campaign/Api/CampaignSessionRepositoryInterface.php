<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteAbCampaign
 * @author    Botis <botis@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteAbCampaign\Api;

use Exception;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignSessionInterface;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignSessionSearchResultsInterface;

/**
 * Interface CampaignSessionRepositoryInterface.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Botis <botis@smile.fr>
 */
interface CampaignSessionRepositoryInterface
{
    /**
     * Save campaign session.
     *
     * @param CampaignSessionInterface $item Campaign session.
     * @return CampaignSessionInterface
     * @throws AlreadyExistsException
     */
    public function save(CampaignSessionInterface $item): CampaignSessionInterface;

    /**
     * Delete campaign session.
     *
     * @param CampaignSessionInterface $item Campaign session.
     * @return void
     * @throws Exception
     */
    public function delete(CampaignSessionInterface $item): void;

    /**
     * Get a list of campaign sessions.
     *
     * @param SearchCriteriaInterface $searchCriteria Search criteria.
     * @return CampaignSessionSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Get campaign session by id.
     *
     * @param int $campaignSessionId Campaign session id.
     * @return CampaignSessionInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $campaignSessionId): CampaignSessionInterface;

    /**
     * Delete campaign session by id.
     *
     * @param int $campaignSessionId Campaign session id.
     * @return void
     * @throws Exception
     */
    public function deleteById(int $campaignSessionId): void;

    /**
     * Get campaign session by campaign data.
     *
     * @param int $campaignId Campaign id.
     * @return CampaignSessionInterface|null
     */
    public function getByCampaignSessionData(int $campaignId): ?CampaignSessionInterface;
}
