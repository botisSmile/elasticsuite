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

namespace Smile\ElasticsuiteAbCampaign\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Campaign interface
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
interface CampaignInterface
{
    /**
     * Name of the main DB Table.
     */
    const TABLE_NAME  = 'smile_elasticsuite_campaign';

    /**
     * Name of the join Mysql Table
     */
    const TABLE_NAME_SEARCH_CONTAINER = 'smile_elasticsuite_campaign_search_container';

    /**
     * Name of the campaign limitation Mysql Table
     */
    const TABLE_NAME_LIMITATION = 'smile_elasticsuite_campaign_limitation';

    /**
     * Constant for field campaign_id
     */
    const CAMPAIGN_ID = 'campaign_id';

    /**
     * Constant for field store_id
     */
    const STORE_ID    = 'store_id';

    /**
     * Constant for field author_id
     */
    const AUTHOR_ID   = 'author_id';

    /**
     * Constant for field author_name
     */
    const AUTHOR_NAME = 'author_name';

    /**
     * Constant for field name
     */
    const NAME        = 'name';

    /**
     * Constant for field description
     */
    const DESCRIPTION = 'description';

    /**
     * Constant for field created_at
     */
    const CREATED_AT  = 'created_at';

    /**
     * Constant for field start_date
     */
    const START_DATE  = 'start_date';

    /**
     * Constant for field end_date
     */
    const END_DATE    = 'end_date';

    /**
     * Constant for field status
     */
    const STATUS      = 'status';

    /**
     * Constant for field scenario_a_name
     */
    const SCENARIO_A_NAME = 'scenario_a_name';

    /**
     * Constant for field status
     */
    const SCENARIO_B_NAME = 'scenario_b_name';

    /**
     * Constant for field scenario_a_name
     */
    const SCENARIO_A_PERCENTAGE = 'scenario_a_percentage';

    /**
     * Constant for field scenario_a_name
     */
    const SCENARIO_B_PERCENTAGE = 'scenario_b_percentage';

    /**
     * Constant for field search_container
     */
    const SEARCH_CONTAINER = 'search_container';

    /**
     * Constant for field scenario_a_optimizer_ids
     */
    const SCENARIO_A_OPTIMIZER_IDS = 'scenario_a_optimizer_ids';

    /**
     * Constant for field scenario_a_optimizer_ids
     */
    const SCENARIO_B_OPTIMIZER_IDS = 'scenario_b_optimizer_ids';

    /**
     * Campaign Statuses
     */
    const STATUS_DRAFT     = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_COMPLETE  = 'complete';

    /**
     * Get Campaign ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get Store ID
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Get Author Id
     *
     * @return int
     */
    public function getAuthorId();

    /**
     * Get Author Name
     *
     * @return string
     */
    public function getAuthorName();

    /**
     * Get Name
     *
     * @return string
     */
    public function getName();

    /**
     * Get Description
     *
     * @return string
     */
    public function getDescription();

    /**
     * Get Created At
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Get Start Date
     *
     * @return string
     */
    public function getStartDate();

    /**
     * Get End Date
     *
     * @return string
     */
    public function getEndDate();

    /**
     * Get Status
     *
     * @return string
     */
    public function getStatus();

    /**
     * Get search containers associated with this campaign.
     *
     * @return array
     */
    public function getSearchContainers();

    /**
     * Get optimizer ids for scenario A.
     *
     * @return array
     */
    public function getScenarioAOptimizerIds();

    /**
     * Get optimizer ids for scenario B.
     *
     * @return array
     */
    public function getScenarioBOptimizerIds();

    /**
     * Get optimizer ids for one scenario.
     *
     * @param string $scenarioType Scenario Type
     * @return array
     */
    public function getOptimizerIds($scenarioType);

    /**
     * Get Scenario A Name.
     *
     * @return string
     */
    public function getScenarioAName();

    /**
     * Get Scenario B name.
     *
     * @return string
     */
    public function getScenarioBName();

    /**
     * Get Scenario A Percentage.
     *
     * @return float
     */
    public function getScenarioAPercentage();

    /**
     * Set Campaign ID
     *
     * @param int $campaignId Campaign ID
     * @return CampaignInterface
     */
    public function setId($campaignId);

    /**
     * Set Store ID
     *
     * @param string $storeId Store ID
     * @return CampaignInterface
     */
    public function setStoreId($storeId);

    /**
     * Set Author Id
     *
     * @param int $authorId Author ID
     * @return CampaignInterface
     */
    public function setAuthorId($authorId);

    /**
     * Set Author Name
     *
     * @param string $authorName Author name
     * @return CampaignInterface
     */
    public function setAuthorName($authorName);

    /**
     * Set Name
     *
     * @param string $name Name
     * @return CampaignInterface
     */
    public function setName($name);

    /**
     * Set Description
     *
     * @param string $description Description
     * @return CampaignInterface
     */
    public function setDescription($description);

    /**
     * Set Created At
     *
     * @param string $createdAt Created At
     * @return CampaignInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Set Start Date
     *
     * @param string $startDate Start Date
     * @return CampaignInterface
     */
    public function setStartDate($startDate);

    /**
     * Set End Date
     *
     * @param string $endDate End Date
     * @return CampaignInterface
     */
    public function setEndDate($endDate);

    /**
     * Set Status
     *
     * @param string $status Status
     * @return CampaignInterface
     */
    public function setStatus($status);

    /**
     * Set search container.
     *
     * @param string $searchContainer The value to search container.
     * @return CampaignInterface
     */
    public function setSearchContainers($searchContainer);

    /**
     * Set optimizer ids for scenario A.
     *
     * @param array $scenarioAOptimizerIds Scenario A optimizer ids
     * @return CampaignInterface
     */
    public function setScenarioAOptimizerIds($scenarioAOptimizerIds);

    /**
     * Set optimizer ids for scenario B.
     *
     * @param array $scenarioBOptimizerIds Scenario B optimizer ids
     * @return CampaignInterface
     */
    public function setScenarioBOptimizerIds($scenarioBOptimizerIds);

    /**
     * Set optimizer ids for one scenario.
     *
     * @param array  $optimizerIds Optimizer ids
     * @param string $scenarioType Scenario Type
     * @return CampaignInterface
     */
    public function setOptimizerIds($optimizerIds, $scenarioType);

    /**
     * Set Scenario a name.
     *
     * @param string $scenarioAName Scenario a name
     * @return CampaignInterface
     */
    public function setScenarioAName($scenarioAName);

    /**
     * Set Scenario b name.
     *
     * @param string $scenarioBName Scenario b name
     * @return CampaignInterface
     */
    public function setScenarioBName($scenarioBName);

    /**
     * Set Scenario a percentage.
     *
     * @param string $scenarioAPercentage Scenario a percentage
     * @return CampaignInterface
     */
    public function setScenarioAPercentage($scenarioAPercentage);
}