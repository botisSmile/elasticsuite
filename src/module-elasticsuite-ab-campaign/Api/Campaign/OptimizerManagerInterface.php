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

namespace Smile\ElasticsuiteAbCampaign\Api\Campaign;

use Exception;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignInterface;
use Smile\ElasticsuiteAbCampaign\Model\Campaign;
use Smile\ElasticsuiteCatalogOptimizer\Model\Optimizer;

/**
 * Interface OptimizerManagerInterface
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
interface OptimizerManagerInterface
{
    /**
     * Extract optimizer ids to restrain actions from an array of optimizer ids.
     *
     * By default, we restrain actions for all optimizers linked to a campaign.
     * Set $takeInAccountCampaignStatus to true to restrain actions for optimizer linked to a published campaign.
     * Set $takeInAccountCampaignDates to true to restrain actions for optimizer linked to a campaign with a start
     * date before today and the end date after today.
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @param array $optimizerIds                Optimizer ids
     * @param bool  $takeInAccountCampaignStatus Take in account Campaign status ?
     * @param bool  $takeInAccountCampaignDates  Take in account campaign dates ?
     * @return array
     */
    public function extractOptimizerIdsToRestrain(
        array $optimizerIds,
        bool $takeInAccountCampaignStatus = false,
        bool $takeInAccountCampaignDates = false
    ): array;

    /**
     * Add campaign context to optimizer.
     *
     * @param Campaign        $campaign  Campaign
     * @param Optimizer|array $optimizer Optimizer: can be an array of data or an optimizer object.
     * @return Optimizer|array
     */
    public function addCampaignContextToOptimizer(Campaign $campaign, $optimizer);
}
