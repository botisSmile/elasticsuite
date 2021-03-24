<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteAbCampaign
 * @author    Pierre Le Maguer <pierre.lemaguer@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteAbCampaign\Api\Data;

/**
 * Elasticsuite Campaign Optimizer Interface
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 */
interface CampaignOptimizerInterface
{
    /**
     * Name of the main Mysql Table
     */
    const TABLE_NAME = 'smile_elasticsuite_campaign_optimizer';

    /**
     * Constant for field campaign_id
     */
    const CAMPAIGN_ID = 'campaign_id';

    /**
     * Constant for field optimizer_id
     */
    const OPTIMIZER_ID = 'optimizer_id';

    /**
     * Constant for field scenario_type
     */
    const SCENARIO_TYPE = 'scenario_type';

    /**
     * Constant for scenario types
     */
    const SCENARIO_TYPE_A = 'A';
    const SCENARIO_TYPE_B = 'B';
}
